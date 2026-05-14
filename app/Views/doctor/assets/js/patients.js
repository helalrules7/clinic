
let searchTimeout;
let currentSearchRequest;

// Helper function to convert hex color to RGB string
/**
 * Render a small badge showing the clinic of the patient's most recent
 * appointment. Returns muted "—" when the patient hasn't been visited yet
 * (or all visits predate the multi-clinic feature).
 *
 * Each clinic gets its own color theme keyed off `last_clinic_code` so the
 * eye can tell Riyadh from Kafr-El-Sheikh at a glance across the page.
 *
 * `compact` mode skips the icon, used in tight card / folder layouts.
 */
/**
 * Build the API id for a SYSTEM folder. Both clinic (`clinic_X`) and
 * legacy doctor (`system_X`) folders ship from the API with the prefix
 * already baked into `folder.id`, so this just returns it as a string.
 * The helper exists so that any future folder group keeps a single
 * call-site to update.
 */
function systemFolderRouteId(folder) {
    if (!folder) return '';
    return String(folder.id);
}

function clinicBadgeStyle(code) {
    if (code === 'RIYADH') return { bg: 'rgba(34,197,94,0.18)',  fg: '#16a34a', icon: 'bi-building' };
    if (code === 'KFS')    return { bg: 'rgba(59,130,246,0.18)', fg: '#2563eb', icon: 'bi-building' };
    return { bg: 'rgba(148,163,184,0.18)', fg: '#64748b', icon: 'bi-building' };
}
function renderClinicBadge(patient, opts) {
    opts = opts || {};
    var compact = !!opts.compact;
    if (!patient || !patient.last_clinic_id) {
        return '<span class="text-muted small">—</span>';
    }
    var name = patient.last_clinic_name_ar || patient.last_clinic_name_en || ('Clinic #' + patient.last_clinic_id);
    var theme = clinicBadgeStyle(patient.last_clinic_code);
    var icon = compact ? '' : '<i class="bi ' + theme.icon + ' me-1"></i>';
    return '<span class="badge clinic-badge clinic-badge-' + (patient.last_clinic_code || 'none').toLowerCase() +
           '" style="background:' + theme.bg + ';color:' + theme.fg + ';font-weight:600;padding:0.35rem 0.55rem;border-radius:8px;">' +
           icon + escapeHtml(name) + '</span>';
}

function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    if (!result) return '0, 0, 0';
    return `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}`;
}

// ============================================
// Mini Sparkline Charts for Stats Cards
// ============================================

function generateSparklineSVG(data) {
    const width = 100;
    const height = 35;
    const padding = 2;

    // Normalize data
    const min = Math.min(...data);
    const max = Math.max(...data);
    const range = max - min || 1;

    // Generate points
    const points = data.map((value, index) => {
        const x = padding + (index / (data.length - 1)) * (width - padding * 2);
        const y = height - padding - ((value - min) / range) * (height - padding * 2);
        return `${x},${y}`;
    });

    // Create path
    const linePath = `M ${points.join(' L ')}`;

    // Create area path (closed for fill)
    const areaPath = `M ${padding},${height} L ${points.join(' L ')} L ${width - padding},${height} Z`;

    return `
        <svg viewBox="0 0 ${width} ${height}" preserveAspectRatio="none">
            <path class="sparkline-area" d="${areaPath}"/>
            <path class="sparkline-path" d="${linePath}"/>
        </svg>
    `;
}

function initMiniStatsCharts() {
    // Generate random-ish trend data for visual effect
    // In production, this would come from actual API data
    const chartConfigs = [
        { id: 'chartTotalPatients', trend: [65, 72, 78, 75, 82, 88, 92, 95, 100] },
        { id: 'chartNewWeek', trend: [3, 5, 2, 8, 4, 6, 7, 9, 5] },
        { id: 'chartNewMonth', trend: [12, 18, 15, 22, 19, 25, 28, 24, 30] },
        { id: 'chartTotalVisits', trend: [120, 135, 142, 138, 155, 162, 158, 175, 180] },
        { id: 'chartRecentVisits', trend: [8, 12, 10, 15, 11, 18, 14, 20, 16] },
        { id: 'chartActivePatients', trend: [45, 52, 48, 58, 55, 62, 68, 65, 72] },
        { id: 'chartMale', trend: [30, 32, 35, 33, 38, 40, 42, 45, 48] },
        { id: 'chartFemale', trend: [28, 30, 32, 35, 33, 38, 36, 40, 42] }
    ];

    chartConfigs.forEach(config => {
        const container = document.getElementById(config.id);
        if (container) {
            container.innerHTML = generateSparklineSVG(config.trend);
        }
    });
}

// Initialize sparkline charts when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure CSS is loaded
    setTimeout(initMiniStatsCharts, 100);
});

// Pagination state
let paginationState = {
    currentPage: 1,
    itemsPerPage: 20,
    totalItems: 0,
    allPatients: [],
    filteredPatients: [],
    currentDoctorFilter: 'all',
    currentGenderFilter: null, // 'Male', 'Female', or null
    currentAgeFilter: { min: null, max: null }, // Age range filter
    currentLastVisitFilter: { from: null, to: null }, // Last visit date range filter
    sortBy: null,
    sortOrder: null
};

// Pagination state for folders view
let folderPaginationState = {
    currentPage: 1,
    itemsPerPage: parseInt(localStorage.getItem('folderItemsPerPage')) || 36, // Default 36 per page
    totalItems: 0,
    filteredPatients: []
};

// Debounce function
function debounce(func, wait) {
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(searchTimeout);
            func(...args);
        };
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(later, wait);
    };
}

// Book appointment function
function bookAppointment(patientId) {
    // Redirect to calendar with patient pre-selected
    window.location.href = `/doctor/calendar?patient_id=${patientId}`;
}

// View patient function
function viewPatient(patientId) {
    window.location.href = `/doctor/patients/${patientId}`;
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element if it doesn't exist
    let notification = document.getElementById('globalNotification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'globalNotification';
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 300px; max-width: 500px;';
        document.body.appendChild(notification);
    }
    
    const alertClass = type === 'error' ? 'danger' : (type === 'success' ? 'success' : 'info');
    const icon = type === 'error' ? 'bi-exclamation-circle' : (type === 'success' ? 'bi-check-circle' : 'bi-info-circle');
    
    notification.innerHTML = `
        <div class="alert alert-${alertClass} alert-dismissible fade show" role="alert">
            <i class="bi ${icon} me-2"></i>
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Auto dismiss after 3 seconds
    setTimeout(() => {
        const alert = notification.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 3000);
}

// View mode state
let currentViewMode = localStorage.getItem('patientsViewMode') || 'table';
let foldersData = [];
let folderRestorationInProgress = false; // Flag to prevent race condition during folder restoration

// ============================================
// FolderTreeview Class
// ============================================
class FolderTreeview {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.warn(`FolderTreeview: Container ${containerId} not found`);
            return;
        }
        
        // Load expanded folders from localStorage
        const savedState = localStorage.getItem('treeviewExpandedFolders');
        let expandedFolders = [];
        if (savedState) {
            try {
                const parsed = JSON.parse(savedState);
                // Support both old format (array) and new format (object with timestamp)
                if (Array.isArray(parsed)) {
                    expandedFolders = parsed;
                } else if (parsed.expandedFolders) {
                    expandedFolders = parsed.expandedFolders;
                    // Optional: Clear old state if older than 7 days
                    const weekAgo = Date.now() - (7 * 24 * 60 * 60 * 1000);
                    if (parsed.timestamp && parsed.timestamp < weekAgo) {
                        expandedFolders = [];
                    }
                }
            } catch (e) {
                console.warn('Error parsing treeview expanded state:', e);
                expandedFolders = [];
            }
        }
        
        this.options = {
            onFolderClick: options.onFolderClick || null,
            expandedFolders: expandedFolders,
            ...options
        };
        
        // Load active folder from localStorage
        const savedActiveFolder = localStorage.getItem('treeviewActiveFolder');
        this.activeFolderId = savedActiveFolder || null;

        this.treeData = null;
        this.subFoldersCache = {}; // Cache for loaded sub-folders: {folderId: {data, timestamp}}
        this.cacheTimeout = 60000; // 1 minute cache timeout
        this.loadingFolders = new Set(); // Track folders currently loading
        this.renderPending = false; // Prevent multiple renders in same frame
        this.expandInProgress = new Set(); // Track folders currently being expanded (prevents double-clicks)
        this._isInitialized = false; // Track if event listeners are bound
    }
    
    buildTree(foldersData) {
        const { systemFolders, customFolders } = foldersData;
        
        const tree = {
            system: [],
            custom: []
        };
        
        // Build system folders tree
        if (systemFolders && systemFolders.length > 0) {
            systemFolders.forEach(folder => {
                tree.system.push({
                    id: folder.id,
                    name: folder.name,
                    type: 'system',
                    patientCount: folder.patient_count || 0,
                    subFoldersCount: folder.sub_folders_count || 0,
                    children: []
                });
            });
        }
        
        // Build custom folders tree (top-level only, sub-folders loaded on demand)
        if (customFolders && customFolders.length > 0) {
            customFolders.forEach(folder => {
                tree.custom.push({
                    id: folder.id,
                    name: folder.name,
                    type: 'custom',
                    patientCount: folder.patient_count || 0,
                    subFoldersCount: folder.sub_folders_count || 0,
                    children: []
                });
            });
        }
        
        this.treeData = tree;
        return tree;
    }
    
    // Schedule a render (batches multiple render calls into one)
    scheduleRender() {
        if (this.renderPending) return;
        this.renderPending = true;
        requestAnimationFrame(() => {
            this.renderPending = false;
            this.render();
        });
    }

    render() {
        if (!this.container || !this.treeData) return;

        let html = '<div class="treeview-list">';
        
        // Render system folders
        if (this.treeData.system.length > 0) {
            html += '<div class="treeview-section">';
            html += '<div class="treeview-section-header">';
            html += '<i class="bi bi-folder-fill me-2"></i>';
            html += '<span>System Folders</span>';
            html += '</div>';
            html += '<div class="treeview-section-content">';
            this.treeData.system.forEach(folder => {
                html += this.renderFolderNode(folder, 0);
            });
            html += '</div>';
            html += '</div>';
        }
        
        // Render custom folders
        if (this.treeData.custom.length > 0) {
            html += '<div class="treeview-section">';
            html += '<div class="treeview-section-header">';
            html += '<i class="bi bi-folder me-2"></i>';
            html += '<span>Custom Folders</span>';
            html += '</div>';
            html += '<div class="treeview-section-content">';
            this.treeData.custom.forEach(folder => {
                html += this.renderFolderNode(folder, 0);
            });
            html += '</div>';
            html += '</div>';
        }
        
        html += '</div>';
        
        this.container.innerHTML = html;
        
        // Attach event listeners
        this.attachEventListeners();
    }
    
    renderFolderNode(folder, level) {
        // Always use string for folderId to ensure consistent comparison
        const folderId = folder.type === 'system' ? systemFolderRouteId(folder) : String(folder.id);
        const isExpanded = this.options.expandedFolders.includes(folderId) || this.options.expandedFolders.includes(String(folderId));
        const hasChildren = folder.subFoldersCount > 0 || (folder.children && folder.children.length > 0);
        const indent = level * 20;
        // Compare as strings - ensure activeFolderId is also string and handle null
        const isActive = this.activeFolderId && String(this.activeFolderId) === String(folderId);
        const isLoading = this.loadingFolders.has(folderId) || this.loadingFolders.has(String(folderId));

        let html = `<div class="treeview-node ${isActive ? 'active' : ''} ${isLoading ? 'loading' : ''}" data-folder-id="${folderId}" data-folder-type="${folder.type}" style="padding-left: ${indent}px;">`;

        // Expand/collapse icon (if has children) or loading spinner
        if (isLoading) {
            html += `<span class="treeview-expand treeview-loading" data-folder-id="${folderId}">`;
            html += `<i class="bi bi-arrow-repeat spin"></i>`;
            html += `</span>`;
        } else if (hasChildren) {
            html += `<span class="treeview-expand" data-folder-id="${folderId}">`;
            html += `<i class="bi ${isExpanded ? 'bi-chevron-down' : 'bi-chevron-right'}"></i>`;
            html += `</span>`;
        } else {
            html += `<span class="treeview-expand-placeholder"></span>`;
        }
        
        // Folder icon
        html += `<i class="bi ${folder.type === 'system' ? 'bi-folder-fill' : 'bi-folder'} treeview-icon"></i>`;
        
        // Folder name and info
        html += `<span class="treeview-label" data-folder-id="${folderId}">`;
        html += `<span class="treeview-name">${this.escapeHtml(folder.name)}</span>`;
        html += `<span class="treeview-meta">`;
        if (folder.patientCount > 0) {
            html += `<span class="treeview-count">${folder.patientCount}</span>`;
        }
        html += `</span>`;
        html += `</span>`;
        
        html += `</div>`;
        
        // Render children if expanded
        if (hasChildren && isExpanded && folder.children && folder.children.length > 0) {
            folder.children.forEach(child => {
                html += this.renderFolderNode(child, level + 1);
            });
        }
        
        return html;
    }
    
    attachEventListeners() {
        if (!this.container) return;

        // Only bind event listeners once - use flag to prevent rebinding on each render
        if (this._isInitialized) return;
        this._isInitialized = true;

        // Use event delegation on container for better performance and reliability
        // This handler persists across render() calls since we only bind it once
        this._clickHandler = (e) => {
            // Check if clicked on expand/collapse chevron
            const expandBtn = e.target.closest('.treeview-expand');
            if (expandBtn && !expandBtn.classList.contains('treeview-loading')) {
                e.stopPropagation();
                e.preventDefault();
                const folderId = expandBtn.getAttribute('data-folder-id');
                if (folderId) {
                    const folderIdStr = String(folderId);
                    // Prevent double-clicks while expand is in progress
                    if (this.expandInProgress.has(folderIdStr)) {
                        return;
                    }
                    // Only toggle expand/collapse, don't load content
                    this.handleExpandToggle(folderIdStr);
                }
                return;
            }

            // Check if clicked on folder label or node (but not on expand button)
            const node = e.target.closest('.treeview-node');
            if (node && !expandBtn) {
                e.stopPropagation();
                e.preventDefault();
                const folderId = node.getAttribute('data-folder-id');
                if (folderId) {
                    // Select folder, expand it, and load content
                    this.handleFolderSelect(String(folderId));
                }
                return;
            }
        };

        this.container.addEventListener('click', this._clickHandler);
    }

    // Handle expand/collapse toggle (chevron click) - NO content loading
    handleExpandToggle(folderId) {
        folderId = String(folderId);

        // Prevent double-clicks - check if this folder is already being processed
        if (this.expandInProgress.has(folderId)) {
            return;
        }

        const index = this.options.expandedFolders.indexOf(folderId);

        if (index > -1) {
            // Collapse - just update UI, no API calls
            this.options.expandedFolders.splice(index, 1);
            this.loadingFolders.delete(folderId);
            this.saveExpandedState();

            // Update chevron icon directly without full re-render
            const expandBtn = this.container.querySelector(`.treeview-expand[data-folder-id="${folderId}"]`);
            if (expandBtn) {
                const icon = expandBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                }
            }

            // Hide children
            const node = this.container.querySelector(`.treeview-node[data-folder-id="${folderId}"]`);
            if (node) {
                let sibling = node.nextElementSibling;
                const nodeIndent = parseInt(node.style.paddingLeft) || 0;
                while (sibling && sibling.classList.contains('treeview-node')) {
                    const siblingIndent = parseInt(sibling.style.paddingLeft) || 0;
                    if (siblingIndent <= nodeIndent) break;
                    sibling.style.display = 'none';
                    sibling = sibling.nextElementSibling;
                }
            }
        } else {
            // Expand - preserve other expanded folders (no auto-collapse)
            // Just add this folder to the expanded list
            this.options.expandedFolders.push(folderId);
            this.saveExpandedState();

            // Check if children are already loaded
            const folder = this.findFolderInTree(folderId);
            if (folder && folder.children && folder.children.length > 0) {
                // Children already loaded, just show them
                const expandBtn = this.container.querySelector(`.treeview-expand[data-folder-id="${folderId}"]`);
                if (expandBtn) {
                    const icon = expandBtn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('bi-chevron-right');
                        icon.classList.add('bi-chevron-down');
                    }
                }

                // Show children - need full re-render since children nodes may not exist in DOM
                this.scheduleRender();
            } else {
                // Need to load children from API - mark as in progress
                this.expandInProgress.add(folderId);
                this.loadingFolders.add(folderId);
                this.scheduleRender();

                this.expandFolder(folderId, false).then(() => {
                    this.loadingFolders.delete(folderId);
                    this.expandInProgress.delete(folderId);
                    this.scheduleRender();
                }).catch(err => {
                    console.error('Error expanding:', err);
                    this.loadingFolders.delete(folderId);
                    this.expandInProgress.delete(folderId);
                    // Remove from expanded if failed
                    const idx = this.options.expandedFolders.indexOf(folderId);
                    if (idx > -1) this.options.expandedFolders.splice(idx, 1);
                    this.saveExpandedState();
                    this.scheduleRender();
                });
            }
        }
    }

    // Handle folder selection (node click) - loads content and expands
    handleFolderSelect(folderId) {
        folderId = String(folderId);

        // Set as active FIRST (before collapse/expand operations)
        this.activeFolderId = folderId;
        localStorage.setItem('treeviewActiveFolder', folderId);

        // NOTE: We no longer collapse other folders to preserve user's expanded state
        // The user's expanded folders remain as-is for better UX

        // Expand this folder if not already expanded (without collapsing others)
        if (!this.options.expandedFolders.includes(folderId)) {
            // Add to expanded folders without triggering full handleExpandToggle
            this.options.expandedFolders.push(folderId);
            this.saveExpandedState();

            // Load children if needed
            const folder = this.findFolderInTree(folderId);
            if (folder && (!folder.children || folder.children.length === 0) && folder.subFoldersCount > 0) {
                this.expandInProgress.add(folderId);
                this.loadingFolders.add(folderId);
                this.expandFolder(folderId, false).then(() => {
                    this.loadingFolders.delete(folderId);
                    this.expandInProgress.delete(folderId);
                    this.scheduleRender();
                }).catch(() => {
                    this.loadingFolders.delete(folderId);
                    this.expandInProgress.delete(folderId);
                });
            }
        }

        // Update active state in DOM immediately
        this.container.querySelectorAll('.treeview-node').forEach(node => {
            node.classList.remove('active');
        });
        const activeNode = this.container.querySelector(`[data-folder-id="${folderId}"]`);
        if (activeNode) {
            activeNode.classList.add('active');
        }

        // Load folder content (this calls openFolder which is part of folderview - don't modify)
        if (this.options.onFolderClick) {
            this.options.onFolderClick(folderId);
        }

        // Re-render to apply expand changes and active state
        this.scheduleRender();
    }

    // Helper to expand folder and render
    expandAndRender(folderId) {
        folderId = String(folderId);
        if (!this.options.expandedFolders.includes(folderId)) {
            this.options.expandedFolders.push(folderId);
        }
        this.loadingFolders.add(folderId);
        this.saveExpandedState();
        this.scheduleRender();

        // Load children data
        this.expandFolder(folderId, true).then(() => {
            this.loadingFolders.delete(folderId);
            this.scheduleRender();
        }).catch(error => {
            console.error('Error expanding folder:', error);
            this.loadingFolders.delete(folderId);
            this.scheduleRender();
        });
    }
    
    toggleFolder(folderId) {
        folderId = String(folderId);
        const index = this.options.expandedFolders.indexOf(folderId);
        if (index > -1) {
            // Collapse
            this.options.expandedFolders.splice(index, 1);
            this.loadingFolders.delete(folderId);
            this.saveExpandedState();
            this.scheduleRender();
        } else {
            // Expand - add to expandedFolders immediately for UI feedback
            this.options.expandedFolders.push(folderId);
            this.loadingFolders.add(folderId);
            this.saveExpandedState();
            this.scheduleRender(); // Show loading state

            // Then load data
            this.expandFolder(folderId, false).then(() => {
                this.loadingFolders.delete(folderId);
                this.scheduleRender();
            }).catch(error => {
                console.error('Error expanding folder:', error);
                this.loadingFolders.delete(folderId);
                const errorIndex = this.options.expandedFolders.indexOf(folderId);
                if (errorIndex > -1) {
                    this.options.expandedFolders.splice(errorIndex, 1);
                    this.saveExpandedState();
                }
                this.scheduleRender();
            });
        }
    }
    
    expandFolder(folderId, expandChildren = false) {
        folderId = String(folderId);
        // Load sub-folders if not already loaded
        const node = this.container.querySelector(`[data-folder-id="${folderId}"]`);
        if (!node) return Promise.resolve();

        const folderType = node.getAttribute('data-folder-type');
        const isSystem = folderType === 'system';
        const actualId = isSystem ? folderId.replace('system_', '') : folderId;

        // Check if already expanded AND has loaded children
        let folder = this.findFolderInTree(folderId);
        if (this.options.expandedFolders.includes(folderId) && folder && folder.children && folder.children.length > 0) {
            // Already expanded with data loaded
            this.loadingFolders.delete(folderId);
            if (expandChildren) {
                return this.expandAllChildren(folderId);
            }
            return Promise.resolve();
        }

        // Add to expanded folders BEFORE loading (for UI state)
        if (!this.options.expandedFolders.includes(folderId)) {
            this.options.expandedFolders.push(folderId);
            this.loadingFolders.add(folderId);
            this.saveExpandedState();
            this.scheduleRender(); // Show loading state
        }

        // Check cache first
        const cached = this.subFoldersCache[folderId];
        const now = Date.now();
        if (cached && (now - cached.timestamp) < this.cacheTimeout) {
            // Use cached data
            folder = this.findFolderInTree(folderId);
            if (folder) {
                folder.children = cached.data.map(sf => ({
                    id: sf.id,
                    name: sf.name,
                    type: 'custom',
                    patientCount: sf.patient_count || 0,
                    subFoldersCount: sf.sub_folders_count || 0,
                    children: []
                }));
                // Update subFoldersCount based on actual children
                folder.subFoldersCount = folder.children.length;
                this.loadingFolders.delete(folderId);
                this.scheduleRender();

                if (expandChildren) {
                    return this.expandAllChildren(folderId);
                }
            }
            return Promise.resolve();
        }

        // Load sub-folders from API
        return fetch(`/api/patient-folders/${actualId}/sub-folders/${folderType}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok && data.sub_folders) {
                // Cache the data
                this.subFoldersCache[folderId] = {
                    data: data.sub_folders,
                    timestamp: Date.now()
                };

                // Find folder in tree and add children
                folder = this.findFolderInTree(folderId);
                if (folder) {
                    folder.children = data.sub_folders.map(sf => ({
                        id: sf.id,
                        name: sf.name,
                        type: 'custom',
                        patientCount: sf.patient_count || 0,
                        subFoldersCount: sf.sub_folders_count || 0,
                        children: []
                    }));
                    // Update subFoldersCount based on actual children (fixes stale expand icon)
                    folder.subFoldersCount = folder.children.length;
                    this.loadingFolders.delete(folderId);
                    this.scheduleRender();

                    if (expandChildren) {
                        return this.expandAllChildren(folderId);
                    }
                }
            } else {
                this.loadingFolders.delete(folderId);
                this.scheduleRender();
            }
            return Promise.resolve();
        })
        .catch(error => {
            console.error('Error loading sub-folders:', error);
            this.loadingFolders.delete(folderId);
            // Remove from expanded if error
            const index = this.options.expandedFolders.indexOf(folderId);
            if (index > -1) {
                this.options.expandedFolders.splice(index, 1);
                this.saveExpandedState();
            }
            this.scheduleRender();
            return Promise.reject(error);
        });
    }
    
    expandAllChildren(folderId) {
        const folder = this.findFolderInTree(folderId);
        if (!folder || !folder.children || folder.children.length === 0) {
            return Promise.resolve();
        }
        
        // Expand all children recursively
        const expandPromises = folder.children.map(child => {
            const childId = child.type === 'system' ? systemFolderRouteId(child) : child.id;
            return this.expandFolder(childId, true); // Recursively expand children
        });
        
        return Promise.all(expandPromises);
    }
    
    expandFolderPath(folderPath) {
        // Expand all folders in the path to make the current folder visible
        if (!folderPath || folderPath.length === 0) return Promise.resolve();
        
        const expandPromises = folderPath.map(folder => {
            const folderId = folder.type === 'system' ? systemFolderRouteId(folder) : folder.id;
            // Only expand, don't expand children for path folders
            return this.expandFolder(folderId, false);
        });
        
        return Promise.all(expandPromises);
    }
    
    saveExpandedState() {
        // Save to localStorage with timestamp for cache management
        const state = {
            expandedFolders: this.options.expandedFolders,
            timestamp: Date.now()
        };
        localStorage.setItem('treeviewExpandedFolders', JSON.stringify(state));
    }
    
    collapseFolder(folderId) {
        // Just re-render, children will be hidden
        this.render();
    }
    
    // Collapse all folders except the specified folder and its parent path
    collapseAllFoldersExcept(folderIdToKeep) {
        folderIdToKeep = String(folderIdToKeep);
        
        // Get parent path for the folder to keep (so we don't collapse parents)
        const parentPath = this.getParentPath(folderIdToKeep);
        const foldersToKeep = new Set([folderIdToKeep, ...parentPath]);
        
        // Collapse all folders that are not in the keep list
        const foldersToCollapse = this.options.expandedFolders.filter(
            folderId => !foldersToKeep.has(String(folderId))
        );
        
        // Remove from expandedFolders
        foldersToCollapse.forEach(folderId => {
            const index = this.options.expandedFolders.indexOf(folderId);
            if (index > -1) {
                this.options.expandedFolders.splice(index, 1);
            }
            this.loadingFolders.delete(folderId);
        });
        
        this.saveExpandedState();
    }
    
    // Get parent path for a folder (all ancestors)
    getParentPath(folderId) {
        const path = [];
        const folder = this.findFolderInTree(folderId);
        if (!folder) return path;
        
        // Find parent by searching tree recursively
        const findParent = (targetId, arr, parent = null) => {
            for (const item of arr) {
                const itemId = item.type === 'system' ? systemFolderRouteId(item) : String(item.id);
                if (itemId === targetId) {
                    return parent;
                }
                if (item.children && item.children.length > 0) {
                    const found = findParent(targetId, item.children, item);
                    if (found !== null) return found;
                }
            }
            return null;
        };
        
        let current = folder;
        const allFolders = [...(this.treeData.system || []), ...(this.treeData.custom || [])];
        
        // Build parent path by traversing up the tree
        while (current) {
            const currentId = current.type === 'system' ? systemFolderRouteId(current) : String(current.id);
            const parent = findParent(currentId, allFolders);
            if (parent) {
                const parentId = parent.type === 'system' ? systemFolderRouteId(parent) : String(parent.id);
                path.unshift(parentId);
                current = parent;
            } else {
                // Reached root level, no more parents
                break;
            }
        }
        
        return path;
    }
    
    findFolderInTree(folderId) {
        if (!this.treeData) return null;
        
        const searchInArray = (arr) => {
            for (const folder of arr) {
                const id = folder.type === 'system' ? systemFolderRouteId(folder) : folder.id;
                if (id === folderId) {
                    return folder;
                }
                if (folder.children && folder.children.length > 0) {
                    const found = searchInArray(folder.children);
                    if (found) return found;
                }
            }
            return null;
        };
        
        const found = searchInArray(this.treeData.system);
        if (found) return found;
        
        return searchInArray(this.treeData.custom);
    }
    
    highlightActive(folderId) {
        folderId = folderId ? String(folderId) : null;
        this.activeFolderId = folderId;

        // Save active folder to localStorage
        if (folderId) {
            localStorage.setItem('treeviewActiveFolder', folderId);
        } else {
            localStorage.removeItem('treeviewActiveFolder');
        }

        // Re-render to apply active state
        if (this.container && this.treeData) {
            this.scheduleRender();

            // After render, ensure active class is applied
            requestAnimationFrame(() => {
                this.container.querySelectorAll('.treeview-node').forEach(node => {
                    node.classList.remove('active');
                });
                const activeNode = this.container.querySelector(`[data-folder-id="${folderId}"]`);
                if (activeNode) {
                    activeNode.classList.add('active');
                    activeNode.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        }
    }

    // Ensure active folder is visible by expanding its parent path
    ensureActiveFolderVisible() {
        if (!this.activeFolderId || !this.treeData) return Promise.resolve();

        // Get parent path for active folder
        const parentPath = this.getParentPath(this.activeFolderId);

        // Expand all parent folders
        const expandPromises = parentPath.map(parentId => {
            if (!this.options.expandedFolders.includes(parentId)) {
                this.options.expandedFolders.push(parentId);
            }
            return this.expandFolder(parentId, false);
        });

        // Also expand the active folder itself
        if (!this.options.expandedFolders.includes(this.activeFolderId)) {
            this.options.expandedFolders.push(this.activeFolderId);
        }
        expandPromises.push(this.expandFolder(this.activeFolderId, false));

        this.saveExpandedState();

        return Promise.all(expandPromises).then(() => {
            this.scheduleRender();
            // Scroll to active folder after render
            requestAnimationFrame(() => {
                const activeNode = this.container.querySelector(`[data-folder-id="${this.activeFolderId}"]`);
                if (activeNode) {
                    activeNode.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        });
    }
    
    clearCache() {
        // Clear sub-folders cache
        this.subFoldersCache = {};
    }
    
    invalidateCache(folderId = null) {
        // Invalidate cache for specific folder or all folders
        if (folderId) {
            delete this.subFoldersCache[folderId];
        } else {
            this.clearCache();
        }
    }
    
    updateTree(foldersData) {
        this.buildTree(foldersData);
        this.render();

        // If there's an active folder saved, ensure it's visible in the treeview
        if (this.activeFolderId) {
            this.ensureActiveFolderVisible();
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Global treeview instance
let folderTreeview = null;

// ============================================
// Unified Filter Manager Class
// ============================================
class UnifiedFilterManager {
    constructor() {
        this.filters = {
            doctor: 'all',
            gender: null,
            age: { min: null, max: null },
            colors: [],
            tags: [],
            dateCreated: { from: null, to: null },
            lastVisit: { from: null, to: null }
        };

        this.currentView = 'table'; // 'table', 'cards', 'folders'
        this.debounceTimer = null;
        this.isInitialized = false;
    }

    init() {
        if (this.isInitialized) return;

        this.bindEvents();
        this.loadDoctorOptions();
        this.loadTagOptions();
        this.initColorPalette();
        this.updateViewVisibility();

        this.isInitialized = true;
    }

    bindEvents() {
        // Chip click handlers for dropdown toggle
        document.querySelectorAll('.filter-chip').forEach(chip => {
            chip.addEventListener('click', (e) => this.toggleDropdown(chip, e));
        });

        // Click outside to close dropdowns
        document.addEventListener('click', (e) => this.handleOutsideClick(e));

        // Clear all buttons
        document.getElementById('clearAllFilters')?.addEventListener('click', () => this.clearAll());
        document.getElementById('mobileClearAll')?.addEventListener('click', () => this.clearAll());
        document.getElementById('mobileFilterClear')?.addEventListener('click', () => this.clearAll());

        // Mobile apply button
        document.getElementById('mobileFilterApply')?.addEventListener('click', () => this.applyMobileFilters());

        // Gender options
        document.querySelectorAll('#genderDropdown .filter-option').forEach(opt => {
            opt.addEventListener('click', (e) => {
                e.stopPropagation();
                this.setGender(opt.dataset.value);
            });
        });

        // Age filter buttons
        document.getElementById('applyAgeFilter')?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.applyAgeFilter();
        });
        document.getElementById('clearAgeFilter')?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.clearAgeFilter();
        });

        // Date Created filter buttons
        document.getElementById('applyDateCreatedFilter')?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.applyDateCreatedFilter();
        });
        document.getElementById('clearDateCreatedFilter')?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.clearDateCreatedFilter();
        });

        // Last Visit filter buttons
        document.getElementById('applyLastVisitFilter')?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.applyLastVisitFilter();
        });
        document.getElementById('clearLastVisitFilter')?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.clearLastVisitFilter();
        });

        // Prevent dropdown close when clicking inside
        document.querySelectorAll('.filter-dropdown').forEach(dropdown => {
            dropdown.addEventListener('click', (e) => e.stopPropagation());
        });

        // Mobile modal event
        const mobileModal = document.getElementById('mobileFilterModal');
        if (mobileModal) {
            mobileModal.addEventListener('show.bs.modal', () => this.renderMobileFilters());
        }
    }

    toggleDropdown(chip, event) {
        // Don't toggle if clicking inside dropdown
        if (event.target.closest('.filter-dropdown')) return;

        const wasOpen = chip.classList.contains('open');

        // Close all dropdowns
        document.querySelectorAll('.filter-chip.open').forEach(c => c.classList.remove('open'));

        // Toggle current
        if (!wasOpen) {
            chip.classList.add('open');
        }
    }

    handleOutsideClick(event) {
        if (!event.target.closest('.filter-chip')) {
            document.querySelectorAll('.filter-chip.open').forEach(c => c.classList.remove('open'));
        }
    }

    // Doctor Filter
    loadDoctorOptions() {
        const dropdown = document.getElementById('doctorDropdown');
        if (!dropdown) return;

        const content = dropdown.querySelector('.filter-dropdown-content');
        if (!content) return;

        const doctors = window.PATIENTS_CONFIG?.doctors || [];
        let html = '<button class="filter-option selected" data-value="all">All Doctors</button>';

        doctors.forEach(doctor => {
            const imagePath = doctor.profile_image
                ? (doctor.profile_image.startsWith('/public/') ? doctor.profile_image : '/public' + doctor.profile_image)
                : null;

            html += `
                <button class="filter-option" data-value="${doctor.id}">
                    ${imagePath
                        ? `<img src="${imagePath}" alt="" class="doctor-avatar-sm me-2" onerror="this.style.display='none'">`
                        : `<span class="doctor-initial-sm me-2">${(doctor.display_name || 'D').charAt(0).toUpperCase()}</span>`
                    }
                    ${this.escapeHtml(doctor.display_name || 'Doctor')}
                </button>
            `;
        });

        content.innerHTML = html;

        // Bind click events
        content.querySelectorAll('.filter-option').forEach(opt => {
            opt.addEventListener('click', (e) => {
                e.stopPropagation();
                this.setDoctor(opt.dataset.value);
            });
        });
    }

    setDoctor(doctorId) {
        this.filters.doctor = doctorId;

        // Update UI
        const chip = document.getElementById('doctorFilterChip');
        const valueEl = document.getElementById('doctorChipValue');

        if (doctorId === 'all') {
            valueEl.textContent = 'All';
            chip.classList.remove('active');
        } else {
            const doctors = window.PATIENTS_CONFIG?.doctors || [];
            const doctor = doctors.find(d => d.id == doctorId);
            valueEl.textContent = doctor?.display_name || doctorId;
            chip.classList.add('active');
        }

        // Update dropdown selection
        document.querySelectorAll('#doctorDropdown .filter-option').forEach(opt => {
            opt.classList.toggle('selected', opt.dataset.value === doctorId);
        });

        // Close dropdown and apply
        chip.classList.remove('open');
        this.applyFilters();
    }

    // Gender Filter
    setGender(gender) {
        this.filters.gender = gender || null;

        // Update UI
        const chip = document.getElementById('genderFilterChip');
        const valueEl = document.getElementById('genderChipValue');

        valueEl.textContent = gender || 'All';
        chip.classList.toggle('active', !!gender);

        // Update dropdown selection
        document.querySelectorAll('#genderDropdown .filter-option').forEach(opt => {
            opt.classList.toggle('selected', (opt.dataset.value || '') === (gender || ''));
        });

        // Close dropdown and apply
        chip.classList.remove('open');
        this.applyFilters();
    }

    // Age Filter
    applyAgeFilter() {
        const min = document.getElementById('ageFilterMin')?.value;
        const max = document.getElementById('ageFilterMax')?.value;

        this.filters.age.min = min ? parseInt(min) : null;
        this.filters.age.max = max ? parseInt(max) : null;

        // Update UI
        const chip = document.getElementById('ageFilterChip');
        const valueEl = document.getElementById('ageChipValue');

        if (this.filters.age.min !== null || this.filters.age.max !== null) {
            const minText = this.filters.age.min ?? '0';
            const maxText = this.filters.age.max ?? '∞';
            valueEl.textContent = `${minText}-${maxText}`;
            chip.classList.add('active');
        } else {
            valueEl.textContent = 'All';
            chip.classList.remove('active');
        }

        chip.classList.remove('open');
        this.applyFilters();
    }

    clearAgeFilter() {
        const minInput = document.getElementById('ageFilterMin');
        const maxInput = document.getElementById('ageFilterMax');
        if (minInput) minInput.value = '';
        if (maxInput) maxInput.value = '';

        this.filters.age = { min: null, max: null };

        const chip = document.getElementById('ageFilterChip');
        const valueEl = document.getElementById('ageChipValue');
        if (valueEl) valueEl.textContent = 'All';
        if (chip) chip.classList.remove('active');

        this.applyFilters();
    }

    // Color Filter
    initColorPalette() {
        const palette = document.querySelector('#colorsDropdown .color-palette');
        if (!palette) return;

        const colors = [
            { code: '#ef4444', name: 'Red' },
            { code: '#f59e0b', name: 'Orange' },
            { code: '#eab308', name: 'Yellow' },
            { code: '#22c55e', name: 'Green' },
            { code: '#06b6d4', name: 'Cyan' },
            { code: '#3b82f6', name: 'Blue' },
            { code: '#8b5cf6', name: 'Purple' },
            { code: '#ec4899', name: 'Pink' }
        ];

        palette.innerHTML = colors.map(c => `
            <button class="color-btn" data-color="${c.code}"
                    style="background: ${c.code}" title="${c.name}"></button>
        `).join('');

        palette.querySelectorAll('.color-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleColor(btn.dataset.color);
            });
        });
    }

    toggleColor(colorCode) {
        const index = this.filters.colors.indexOf(colorCode);
        if (index > -1) {
            this.filters.colors.splice(index, 1);
        } else {
            this.filters.colors.push(colorCode);
        }

        this.updateColorUI();
        this.applyFilters();
    }

    updateColorUI() {
        // Update palette selection
        document.querySelectorAll('#colorsDropdown .color-btn').forEach(btn => {
            btn.classList.toggle('selected', this.filters.colors.includes(btn.dataset.color));
        });

        // Update chip preview
        const preview = document.getElementById('colorDotsPreview');
        const chip = document.getElementById('colorsFilterChip');

        if (preview) {
            if (this.filters.colors.length > 0) {
                preview.innerHTML = this.filters.colors.slice(0, 4).map(c =>
                    `<span class="color-dot" style="background: ${c}"></span>`
                ).join('');
                if (this.filters.colors.length > 4) {
                    preview.innerHTML += `<span class="color-dot-more">+${this.filters.colors.length - 4}</span>`;
                }
                if (chip) chip.classList.add('active');
            } else {
                preview.innerHTML = '';
                if (chip) chip.classList.remove('active');
            }
        }
    }

    // Tags Filter
    async loadTagOptions() {
        const tagsDropdown = document.getElementById('tagsDropdown');
        if (!tagsDropdown) return;

        const tagsList = tagsDropdown.querySelector('.tags-list');
        if (!tagsList) return;

        try {
            const response = await fetch('/api/patient-tags', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            if (data.ok && data.tags) {
                if (data.tags.length === 0) {
                    tagsList.innerHTML = '<p class="text-muted small p-2 mb-0">No tags available</p>';
                } else {
                    tagsList.innerHTML = data.tags.map(tag => `
                        <label class="tag-option">
                            <input type="checkbox" value="${tag.id}" class="form-check-input">
                            <span class="badge" style="background: ${tag.color || '#6366f1'}">
                                ${tag.icon ? `<i class="bi ${tag.icon} me-1"></i>` : ''}
                                ${this.escapeHtml(tag.name)}
                            </span>
                        </label>
                    `).join('');

                    tagsList.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                        cb.addEventListener('change', () => this.handleTagChange());
                    });
                }
            }
        } catch (error) {
            console.error('Error loading tags:', error);
            tagsList.innerHTML = '<p class="text-muted small p-2 mb-0">Failed to load tags</p>';
        }
    }

    handleTagChange() {
        const checkboxes = document.querySelectorAll('#tagsDropdown input[type="checkbox"]:checked');
        this.filters.tags = Array.from(checkboxes).map(cb => parseInt(cb.value));

        const chip = document.getElementById('tagsFilterChip');
        const valueEl = document.getElementById('tagsChipValue');

        if (valueEl) {
            valueEl.textContent = this.filters.tags.length > 0 ? `${this.filters.tags.length} selected` : 'All';
        }
        if (chip) {
            chip.classList.toggle('active', this.filters.tags.length > 0);
        }

        this.applyFilters();
    }

    // Date Created Filter
    applyDateCreatedFilter() {
        this.filters.dateCreated.from = document.getElementById('dateCreatedFrom')?.value || null;
        this.filters.dateCreated.to = document.getElementById('dateCreatedTo')?.value || null;

        const chip = document.getElementById('dateCreatedFilterChip');
        const valueEl = document.getElementById('dateCreatedChipValue');

        if (this.filters.dateCreated.from || this.filters.dateCreated.to) {
            if (valueEl) valueEl.textContent = 'Set';
            if (chip) chip.classList.add('active');
        } else {
            if (valueEl) valueEl.textContent = 'All';
            if (chip) chip.classList.remove('active');
        }

        if (chip) chip.classList.remove('open');
        this.applyFilters();
    }

    clearDateCreatedFilter() {
        const fromInput = document.getElementById('dateCreatedFrom');
        const toInput = document.getElementById('dateCreatedTo');
        if (fromInput) fromInput.value = '';
        if (toInput) toInput.value = '';

        this.filters.dateCreated = { from: null, to: null };

        const chip = document.getElementById('dateCreatedFilterChip');
        const valueEl = document.getElementById('dateCreatedChipValue');
        if (valueEl) valueEl.textContent = 'All';
        if (chip) chip.classList.remove('active');

        this.applyFilters();
    }

    // Last Visit Filter
    applyLastVisitFilter() {
        this.filters.lastVisit.from = document.getElementById('lastVisitFrom')?.value || null;
        this.filters.lastVisit.to = document.getElementById('lastVisitTo')?.value || null;

        const chip = document.getElementById('lastVisitFilterChip');
        const valueEl = document.getElementById('lastVisitChipValue');

        if (this.filters.lastVisit.from || this.filters.lastVisit.to) {
            if (valueEl) valueEl.textContent = 'Set';
            if (chip) chip.classList.add('active');
        } else {
            if (valueEl) valueEl.textContent = 'All';
            if (chip) chip.classList.remove('active');
        }

        if (chip) chip.classList.remove('open');
        this.applyFilters();
    }

    clearLastVisitFilter() {
        const fromInput = document.getElementById('lastVisitFrom');
        const toInput = document.getElementById('lastVisitTo');
        if (fromInput) fromInput.value = '';
        if (toInput) toInput.value = '';

        this.filters.lastVisit = { from: null, to: null };

        const chip = document.getElementById('lastVisitFilterChip');
        const valueEl = document.getElementById('lastVisitChipValue');
        if (valueEl) valueEl.textContent = 'All';
        if (chip) chip.classList.remove('active');

        this.applyFilters();
    }

    // View Management
    setView(view) {
        this.currentView = view;
        this.updateViewVisibility();

        // Add view class to filter bar for CSS targeting
        const filterBar = document.getElementById('unifiedFilterBar');
        if (filterBar) {
            filterBar.classList.remove('view-table', 'view-cards', 'view-folders');
            filterBar.classList.add(`view-${view}`);
        }

        // Reapply filters for the new view
        this.applyFilters();
    }

    updateViewVisibility() {
        // View-specific filters are controlled via CSS using data-views attribute
        // The CSS handles showing/hiding based on .view-table, .view-cards, .view-folders classes
    }

    // Apply Filters
    applyFilters() {
        // Debounce rapid changes
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this._doApplyFilters();
        }, 50);
    }

    _doApplyFilters() {
        // Update active filters badge first
        this.updateActiveFiltersBadge();

        // Get base patient data based on view
        let patients;
        if (this.currentView === 'folders' && typeof currentFolderPatients !== 'undefined') {
            patients = [...(currentFolderPatients || [])];
        } else {
            patients = [...(window.PATIENTS_CONFIG?.patients || paginationState.allPatients || [])];
        }

        // Doctor filter (all views)
        if (this.filters.doctor !== 'all') {
            patients = patients.filter(p =>
                p.created_by == this.filters.doctor || p.created_by_doctor_id == this.filters.doctor
            );
        }

        // Gender filter (all views)
        if (this.filters.gender) {
            patients = patients.filter(p => p.gender === this.filters.gender);
        }

        // Age filter (all views)
        if (this.filters.age.min !== null || this.filters.age.max !== null) {
            patients = patients.filter(p => {
                if (!p.dob) return false;
                const age = calculateAge(p.dob);
                if (this.filters.age.min !== null && age < this.filters.age.min) return false;
                if (this.filters.age.max !== null && age > this.filters.age.max) return false;
                return true;
            });
        }

        // Color filter (All views)
        if (this.filters.colors.length > 0) {
            patients = patients.filter(p => p.color_marker && this.filters.colors.includes(p.color_marker));
        }

        // Tags filter (All views)
        if (this.filters.tags.length > 0) {
            patients = patients.filter(p => {
                if (!p.tags || p.tags.length === 0) return false;
                const patientTagIds = p.tags.map(t => typeof t === 'object' ? t.id : t);
                return this.filters.tags.some(tagId => patientTagIds.includes(tagId));
            });
        }

        // Date Created filter (Cards/Folders only)
        if ((this.filters.dateCreated.from || this.filters.dateCreated.to) &&
            (this.currentView === 'cards' || this.currentView === 'folders')) {
            patients = patients.filter(p => {
                if (!p.created_at) return false;
                const created = new Date(p.created_at);
                if (this.filters.dateCreated.from && created < new Date(this.filters.dateCreated.from)) return false;
                if (this.filters.dateCreated.to) {
                    const toDate = new Date(this.filters.dateCreated.to);
                    toDate.setHours(23, 59, 59, 999);
                    if (created > toDate) return false;
                }
                return true;
            });
        }

        // Last Visit filter (Table only)
        if ((this.filters.lastVisit.from || this.filters.lastVisit.to) && this.currentView === 'table') {
            patients = patients.filter(p => {
                if (!p.last_visit) return false;
                const lastVisit = new Date(p.last_visit);
                if (this.filters.lastVisit.from && lastVisit < new Date(this.filters.lastVisit.from)) return false;
                if (this.filters.lastVisit.to) {
                    const toDate = new Date(this.filters.lastVisit.to);
                    toDate.setHours(23, 59, 59, 999);
                    if (lastVisit > toDate) return false;
                }
                return true;
            });
        }

        // Render based on current view
        switch (this.currentView) {
            case 'table':
                paginationState.filteredPatients = patients;
                paginationState.currentPage = 1;
                if (typeof renderPatientsTable === 'function') renderPatientsTable();
                if (typeof updatePaginationInfo === 'function') updatePaginationInfo();
                if (typeof renderPaginationNav === 'function') renderPaginationNav();
                break;
            case 'cards':
                paginationState.filteredPatients = patients;
                paginationState.currentPage = 1;
                if (typeof renderPatientsCards === 'function') renderPatientsCards();
                if (typeof updatePaginationInfoCards === 'function') updatePaginationInfoCards();
                if (typeof renderPaginationNavCards === 'function') renderPaginationNavCards();
                break;
            case 'folders':
                folderPaginationState.filteredPatients = patients;
                folderPaginationState.currentPage = 1;
                if (typeof renderFolderPatients === 'function') renderFolderPatients(patients);
                if (typeof renderFolderPaginationNav === 'function') renderFolderPaginationNav();
                if (typeof updateFolderPaginationInfo === 'function') updateFolderPaginationInfo();
                break;
        }

        // Sync paginationState for legacy code (e.g. updateClearFiltersVisibility)
        paginationState.currentDoctorFilter = this.filters.doctor === 'all' ? 'all' : this.filters.doctor;
        paginationState.currentGenderFilter = this.filters.gender;
        paginationState.currentAgeFilter = { min: this.filters.age.min, max: this.filters.age.max };
        paginationState.currentLastVisitFilter = { from: this.filters.lastVisit.from, to: this.filters.lastVisit.to };
        if (typeof updateClearFiltersVisibility === 'function') updateClearFiltersVisibility();
    }

    getActiveFiltersCount() {
        let count = 0;
        if (this.filters.doctor !== 'all') count++;
        if (this.filters.gender) count++;
        if (this.filters.age.min !== null || this.filters.age.max !== null) count++;
        // Colors and Tags available in all views
        if (this.filters.colors.length > 0) count++;
        if (this.filters.tags.length > 0) count++;
        // View-specific filters
        if ((this.currentView === 'cards' || this.currentView === 'folders')) {
            if (this.filters.dateCreated.from || this.filters.dateCreated.to) count++;
        }
        if (this.currentView === 'table') {
            if (this.filters.lastVisit.from || this.filters.lastVisit.to) count++;
        }
        return count;
    }

    updateActiveFiltersBadge() {
        const count = this.getActiveFiltersCount();

        // Desktop badge
        const badge = document.getElementById('activeFiltersBadge');
        const countEl = document.getElementById('activeFiltersCount');
        const clearBtn = document.getElementById('clearAllFilters');

        if (badge && countEl && clearBtn) {
            countEl.textContent = count;
            badge.style.display = count > 0 ? '' : 'none';
            clearBtn.style.display = count > 0 ? '' : 'none';
        }

        // Mobile badge
        const mobileBadge = document.getElementById('mobileActiveBadge');
        const mobileCount = document.getElementById('mobileActiveCount');
        const mobileClear = document.getElementById('mobileClearAll');

        if (mobileBadge && mobileCount) {
            mobileCount.textContent = count;
            mobileBadge.style.display = count > 0 ? '' : 'none';
        }

        if (mobileClear) {
            mobileClear.style.display = count > 0 ? '' : 'none';
        }
    }

    clearAll() {
        // Reset all filters
        this.filters = {
            doctor: 'all',
            gender: null,
            age: { min: null, max: null },
            colors: [],
            tags: [],
            dateCreated: { from: null, to: null },
            lastVisit: { from: null, to: null }
        };

        // Reset chip UI
        document.querySelectorAll('.filter-chip.active').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.filter-chip-value').forEach(v => {
            if (v.id !== 'colorDotsPreview') v.textContent = 'All';
        });

        // Reset dropdown selections
        document.querySelectorAll('.filter-option.selected').forEach(o => o.classList.remove('selected'));
        document.querySelectorAll('.filter-option[data-value=""], .filter-option[data-value="all"]')
            .forEach(o => o.classList.add('selected'));

        // Reset inputs
        ['ageFilterMin', 'ageFilterMax', 'dateCreatedFrom', 'dateCreatedTo', 'lastVisitFrom', 'lastVisitTo']
            .forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });

        // Reset color buttons
        document.querySelectorAll('.color-btn.selected').forEach(b => b.classList.remove('selected'));
        const colorPreview = document.getElementById('colorDotsPreview');
        if (colorPreview) colorPreview.innerHTML = '';

        // Reset tag checkboxes
        document.querySelectorAll('#tagsDropdown input[type="checkbox"]').forEach(cb => cb.checked = false);

        // Apply
        this.applyFilters();
    }

    // Mobile Modal - Get Tags HTML
    async getMobileTagsHTML() {
        try {
            const response = await fetch('/api/patient-tags', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            if (data.ok && data.tags && data.tags.length > 0) {
                return `
                    <!-- Tags Section -->
                    <div class="mobile-filter-section">
                        <div class="mobile-filter-section-title">Tags</div>
                        <div class="mobile-filter-options" style="max-height: 200px; overflow-y: auto;">
                            ${data.tags.map(tag => `
                                <label class="mobile-filter-btn ${this.filters.tags.includes(tag.id) ? 'selected' : ''}"
                                       style="cursor: pointer; margin-bottom: 0.25rem;">
                                    <input type="checkbox" value="${tag.id}" 
                                           ${this.filters.tags.includes(tag.id) ? 'checked' : ''}
                                           style="display: none;">
                                    <span class="badge" style="background: ${tag.color || '#6366f1'};">
                                        ${tag.icon ? `<i class="bi ${tag.icon} me-1"></i>` : ''}
                                        ${this.escapeHtml(tag.name)}
                                    </span>
                                </label>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            return '';
        } catch (error) {
            console.error('Error loading tags for mobile:', error);
            return '';
        }
    }

    // Mobile Modal
    async renderMobileFilters() {
        const content = document.getElementById('mobileFilterContent');
        if (!content) return;

        const doctors = window.PATIENTS_CONFIG?.doctors || [];
        const colors = [
            { code: '#ef4444', name: 'Red' },
            { code: '#f59e0b', name: 'Orange' },
            { code: '#eab308', name: 'Yellow' },
            { code: '#22c55e', name: 'Green' },
            { code: '#06b6d4', name: 'Cyan' },
            { code: '#3b82f6', name: 'Blue' },
            { code: '#8b5cf6', name: 'Purple' },
            { code: '#ec4899', name: 'Pink' }
        ];

        let html = `
            <!-- Doctor Section -->
            <div class="mobile-filter-section">
                <div class="mobile-filter-section-title">Doctor</div>
                <div class="mobile-filter-options">
                    <button class="mobile-filter-btn ${this.filters.doctor === 'all' ? 'selected' : ''}"
                            data-filter="doctor" data-value="all">All</button>
                    ${doctors.map(d => `
                        <button class="mobile-filter-btn ${this.filters.doctor == d.id ? 'selected' : ''}"
                                data-filter="doctor" data-value="${d.id}">${this.escapeHtml(d.display_name)}</button>
                    `).join('')}
                </div>
            </div>

            <!-- Gender Section -->
            <div class="mobile-filter-section">
                <div class="mobile-filter-section-title">Gender</div>
                <div class="mobile-filter-options">
                    <button class="mobile-filter-btn ${!this.filters.gender ? 'selected' : ''}"
                            data-filter="gender" data-value="">All</button>
                    <button class="mobile-filter-btn ${this.filters.gender === 'Male' ? 'selected' : ''}"
                            data-filter="gender" data-value="Male">
                        <i class="bi bi-gender-male me-1"></i>Male
                    </button>
                    <button class="mobile-filter-btn ${this.filters.gender === 'Female' ? 'selected' : ''}"
                            data-filter="gender" data-value="Female">
                        <i class="bi bi-gender-female me-1"></i>Female
                    </button>
                </div>
            </div>

            <!-- Age Section -->
            <div class="mobile-filter-section">
                <div class="mobile-filter-section-title">Age Range</div>
                <div class="mobile-range-inputs">
                    <input type="number" id="mobileAgeMin" placeholder="Min" min="0" max="150"
                           value="${this.filters.age.min || ''}">
                    <span>to</span>
                    <input type="number" id="mobileAgeMax" placeholder="Max" min="0" max="150"
                           value="${this.filters.age.max || ''}">
                </div>
            </div>
        `;

        // Colors Section (All views)
        html += `
            <!-- Colors Section -->
            <div class="mobile-filter-section">
                <div class="mobile-filter-section-title">Color Markers</div>
                <div class="mobile-color-palette">
                    ${colors.map(c => `
                        <button class="mobile-color-btn ${this.filters.colors.includes(c.code) ? 'selected' : ''}"
                                data-color="${c.code}" style="background: ${c.code}" title="${c.name}"></button>
                    `).join('')}
                </div>
            </div>
        `;

        // Tags Section (All views) - Load tags async
        const tagsHtml = await this.getMobileTagsHTML();
        html += tagsHtml;

        // View-specific sections
        if (this.currentView === 'cards' || this.currentView === 'folders') {
            html += `
                <!-- Date Created Section -->
                <div class="mobile-filter-section">
                    <div class="mobile-filter-section-title">Date Created</div>
                    <div class="mobile-range-inputs">
                        <input type="date" id="mobileDateCreatedFrom" value="${this.filters.dateCreated.from || ''}">
                        <span>to</span>
                        <input type="date" id="mobileDateCreatedTo" value="${this.filters.dateCreated.to || ''}">
                    </div>
                </div>
            `;
        }

        if (this.currentView === 'table') {
            html += `
                <!-- Last Visit Section -->
                <div class="mobile-filter-section">
                    <div class="mobile-filter-section-title">Last Visit</div>
                    <div class="mobile-range-inputs">
                        <input type="date" id="mobileLastVisitFrom" value="${this.filters.lastVisit.from || ''}">
                        <span>to</span>
                        <input type="date" id="mobileLastVisitTo" value="${this.filters.lastVisit.to || ''}">
                    </div>
                </div>
            `;
        }

        content.innerHTML = html;

        // Bind mobile filter events
        content.querySelectorAll('.mobile-filter-btn[data-filter]').forEach(btn => {
            btn.addEventListener('click', () => {
                const filter = btn.dataset.filter;
                const value = btn.dataset.value;

                // Update selection UI
                btn.closest('.mobile-filter-options').querySelectorAll('.mobile-filter-btn')
                    .forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');

                // Store temporary value (applied on Apply button)
                btn.closest('.mobile-filter-section').dataset.selectedValue = value;
            });
        });

        // Mobile color buttons
        content.querySelectorAll('.mobile-color-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.classList.toggle('selected');
            });
        });

        // Mobile tag checkboxes
        content.querySelectorAll('.mobile-filter-section input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', () => {
                const label = cb.closest('label');
                if (label) {
                    label.classList.toggle('selected', cb.checked);
                }
            });
        });
    }

    applyMobileFilters() {
        const content = document.getElementById('mobileFilterContent');
        if (!content) return;

        // Doctor
        const doctorBtn = content.querySelector('.mobile-filter-btn[data-filter="doctor"].selected');
        if (doctorBtn) this.filters.doctor = doctorBtn.dataset.value;

        // Gender
        const genderBtn = content.querySelector('.mobile-filter-btn[data-filter="gender"].selected');
        if (genderBtn) this.filters.gender = genderBtn.dataset.value || null;

        // Age
        const ageMin = document.getElementById('mobileAgeMin');
        const ageMax = document.getElementById('mobileAgeMax');
        this.filters.age.min = ageMin?.value ? parseInt(ageMin.value) : null;
        this.filters.age.max = ageMax?.value ? parseInt(ageMax.value) : null;

        // Colors (All views)
        this.filters.colors = [];
        content.querySelectorAll('.mobile-color-btn.selected').forEach(btn => {
            this.filters.colors.push(btn.dataset.color);
        });

        // Tags (All views)
        this.filters.tags = [];
        content.querySelectorAll('.mobile-filter-section input[type="checkbox"]:checked').forEach(cb => {
            this.filters.tags.push(parseInt(cb.value));
        });

        // Date Created (Cards/Folders only)
        if (this.currentView === 'cards' || this.currentView === 'folders') {
            const dateFrom = document.getElementById('mobileDateCreatedFrom');
            const dateTo = document.getElementById('mobileDateCreatedTo');
            this.filters.dateCreated.from = dateFrom?.value || null;
            this.filters.dateCreated.to = dateTo?.value || null;
        }

        // Last Visit (Table)
        if (this.currentView === 'table') {
            const visitFrom = document.getElementById('mobileLastVisitFrom');
            const visitTo = document.getElementById('mobileLastVisitTo');
            this.filters.lastVisit.from = visitFrom?.value || null;
            this.filters.lastVisit.to = visitTo?.value || null;
        }

        // Update desktop UI to match
        this.syncDesktopUI();

        // Apply filters
        this.applyFilters();
    }

    syncDesktopUI() {
        // Sync doctor chip
        const doctorChip = document.getElementById('doctorFilterChip');
        const doctorValue = document.getElementById('doctorChipValue');
        if (doctorChip && doctorValue) {
            if (this.filters.doctor === 'all') {
                doctorValue.textContent = 'All';
                doctorChip.classList.remove('active');
            } else {
                const doctors = window.PATIENTS_CONFIG?.doctors || [];
                const doctor = doctors.find(d => d.id == this.filters.doctor);
                doctorValue.textContent = doctor?.display_name || this.filters.doctor;
                doctorChip.classList.add('active');
            }
        }

        // Sync gender chip
        const genderChip = document.getElementById('genderFilterChip');
        const genderValue = document.getElementById('genderChipValue');
        if (genderChip && genderValue) {
            genderValue.textContent = this.filters.gender || 'All';
            genderChip.classList.toggle('active', !!this.filters.gender);
        }

        // Sync age chip
        const ageChip = document.getElementById('ageFilterChip');
        const ageValue = document.getElementById('ageChipValue');
        if (ageChip && ageValue) {
            if (this.filters.age.min !== null || this.filters.age.max !== null) {
                ageValue.textContent = `${this.filters.age.min ?? '0'}-${this.filters.age.max ?? '∞'}`;
                ageChip.classList.add('active');
            } else {
                ageValue.textContent = 'All';
                ageChip.classList.remove('active');
            }
        }

        // Sync color chip
        this.updateColorUI();

        // Sync tags chip
        const tagsChip = document.getElementById('tagsFilterChip');
        const tagsValue = document.getElementById('tagsChipValue');
        if (tagsChip && tagsValue) {
            tagsValue.textContent = this.filters.tags.length > 0 ? `${this.filters.tags.length} selected` : 'All';
            tagsChip.classList.toggle('active', this.filters.tags.length > 0);
        }

        // Sync date created chip
        const dateChip = document.getElementById('dateCreatedFilterChip');
        const dateValue = document.getElementById('dateCreatedChipValue');
        if (dateChip && dateValue) {
            if (this.filters.dateCreated.from || this.filters.dateCreated.to) {
                dateValue.textContent = 'Set';
                dateChip.classList.add('active');
            } else {
                dateValue.textContent = 'All';
                dateChip.classList.remove('active');
            }
        }

        // Sync last visit chip
        const visitChip = document.getElementById('lastVisitFilterChip');
        const visitValue = document.getElementById('lastVisitChipValue');
        if (visitChip && visitValue) {
            if (this.filters.lastVisit.from || this.filters.lastVisit.to) {
                visitValue.textContent = 'Set';
                visitChip.classList.add('active');
            } else {
                visitValue.textContent = 'All';
                visitChip.classList.remove('active');
            }
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }
}

// Global filter manager instance
let unifiedFilterManager = null;

// Sidebar toggle functionality
function initSidebarToggle() {
    const sidebar = document.getElementById('foldersSidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    if (!sidebar || !toggleBtn) return;
    
    // Check saved state
    const isCollapsed = localStorage.getItem('foldersSidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        toggleBtn.querySelector('i').classList.remove('bi-chevron-left');
        toggleBtn.querySelector('i').classList.add('bi-chevron-right');
    }
    
    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('foldersSidebarCollapsed', isCollapsed.toString());
        
        const icon = toggleBtn.querySelector('i');
        if (isCollapsed) {
            icon.classList.remove('bi-chevron-left');
            icon.classList.add('bi-chevron-right');
        } else {
            icon.classList.remove('bi-chevron-right');
            icon.classList.add('bi-chevron-left');
        }
    });
}

// Initialize sidebar toggle when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebarToggle);
} else {
    initSidebarToggle();
}

// ============================================
// Folder Navigation State Persistence
// ============================================

// Folder data cache for performance
const folderCache = {
    data: new Map(),
    maxAge: 60000, // 1 minute cache

    set(key, data) {
        this.data.set(key, {
            data,
            timestamp: Date.now()
        });
    },

    get(key) {
        const cached = this.data.get(key);
        if (cached && (Date.now() - cached.timestamp) < this.maxAge) {
            return cached.data;
        }
        this.data.delete(key);
        return null;
    },

    invalidate(key) {
        if (key) {
            this.data.delete(key);
            // Also invalidate parent folder if this is a subfolder
            const parentKey = key.replace(/^folder_/, 'folder_parent_');
            this.data.delete(parentKey);
        } else {
            this.data.clear();
        }
    }
};

// Save folder navigation state to storage
function saveFolderNavigationState() {
    if (!currentFolderId) return;

    // Determine the treeview-compatible folder ID. A system folder ID is
    // already prefixed when it starts with either `system_` (doctor group)
    // or `clinic_` (clinic group).
    const _idStr = currentFolderId.toString();
    const alreadyPrefixed = _idStr.startsWith('system_') || _idStr.startsWith('clinic_');
    const isSystem = currentFolderType === 'system' || alreadyPrefixed;
    const treeviewFolderId = isSystem && !alreadyPrefixed
        ? `system_${currentFolderId}`
        : _idStr;

    const state = {
        folderId: currentFolderId,
        folderType: currentFolderType,
        folderName: currentFolderName,
        pathStack: folderPathStack,
        scrollPosition: window.scrollY,
        timestamp: Date.now(),
        treeviewFolderId: treeviewFolderId // Store treeview-compatible ID
    };

    // Save to sessionStorage for navigation (survives page changes)
    sessionStorage.setItem('folderNavigationState', JSON.stringify(state));

    // Also keep localStorage for persistence across sessions
    localStorage.setItem('currentFolderId', currentFolderId?.toString() || '');
    localStorage.setItem('currentFolderType', currentFolderType || '');
    localStorage.setItem('folderPathStack', JSON.stringify(folderPathStack));

    // Sync treeview active folder
    localStorage.setItem('treeviewActiveFolder', treeviewFolderId);
}

// Restore folder navigation state from storage
function restoreFolderNavigationState() {
    // Priority 1: SessionStorage (for recent navigation)
    const sessionState = sessionStorage.getItem('folderNavigationState');
    if (sessionState) {
        try {
            const state = JSON.parse(sessionState);
            // Check if state is recent (within 1 hour)
            if (Date.now() - state.timestamp < 3600000) {
                // Ensure treeviewFolderId is set
                if (!state.treeviewFolderId && state.folderId) {
                    const _sid = state.folderId.toString();
                    const _alreadyPrefixed = _sid.startsWith('system_') || _sid.startsWith('clinic_');
                    const isSystem = state.folderType === 'system' || _alreadyPrefixed;
                    state.treeviewFolderId = isSystem && !_alreadyPrefixed
                        ? `system_${state.folderId}`
                        : _sid;
                }
                return state;
            }
        } catch (e) {
            console.warn('Failed to parse session folder state:', e);
        }
    }

    // Priority 2: LocalStorage (for session persistence)
    const savedFolderId = localStorage.getItem('currentFolderId');
    const savedFolderType = localStorage.getItem('currentFolderType');
    const savedPathStack = localStorage.getItem('folderPathStack');
    const savedTreeviewFolderId = localStorage.getItem('treeviewActiveFolder');

    if (savedFolderId) {
        let pathStack = [];
        try {
            pathStack = JSON.parse(savedPathStack || '[]');
        } catch (e) {
            pathStack = [];
        }

        const _savedIdStr = savedFolderId.toString();
        const _savedAlreadyPrefixed = _savedIdStr.startsWith('system_') || _savedIdStr.startsWith('clinic_');
        const isSystem = savedFolderType === 'system' || _savedAlreadyPrefixed;
        const treeviewFolderId = savedTreeviewFolderId || (isSystem && !_savedAlreadyPrefixed
            ? `system_${savedFolderId}`
            : _savedIdStr);

        return {
            folderId: savedFolderId,
            folderType: savedFolderType || (_savedAlreadyPrefixed ? 'system' : 'custom'),
            pathStack: pathStack,
            scrollPosition: 0,
            treeviewFolderId: treeviewFolderId
        };
    }

    return null;
}

// Clear folder navigation state
function clearFolderNavigationState() {
    sessionStorage.removeItem('folderNavigationState');
    localStorage.removeItem('currentFolderId');
    localStorage.removeItem('currentFolderType');
    localStorage.removeItem('folderPathStack');
    // Also clear treeview active folder
    localStorage.removeItem('treeviewActiveFolder');
}

// Debounced folder open to prevent race conditions
let folderOpenTimeout = null;
let pendingFolderId = null;

function openFolderDebounced(folderId) {
    pendingFolderId = folderId;

    if (folderOpenTimeout) {
        clearTimeout(folderOpenTimeout);
    }

    folderOpenTimeout = setTimeout(() => {
        if (pendingFolderId === folderId) {
            openFolder(folderId);
        }
    }, 50); // Reduced from 100ms
}

// Initialize pagination with PHP data
function initializePagination() {
    // Check if PATIENTS_CONFIG exists (only available on patients.php page)
    if (!window.PATIENTS_CONFIG) {
        return; // Exit early if not on patients page
    }
    
    // Get patients data from PHP
    const patientsData = window.PATIENTS_CONFIG.patients;
    const doctorsData = window.PATIENTS_CONFIG.doctors;
    
    paginationState.allPatients = patientsData;
    paginationState.filteredPatients = [...patientsData];
    paginationState.totalItems = patientsData.length;
    paginationState.doctors = doctorsData;

    // Initialize unified filter manager
    if (!unifiedFilterManager) {
        unifiedFilterManager = new UnifiedFilterManager();
        unifiedFilterManager.init();
        unifiedFilterManager.setView(currentViewMode);
    }
    
    // Load folders if folders view is active
    if (currentViewMode === 'folders') {
        // Restore folder navigation state before loading
        const savedState = restoreFolderNavigationState();
        if (savedState && savedState.folderId) {
            // Pre-set state variables
            currentFolderId = savedState.folderId;
            currentFolderType = savedState.folderType;
            folderPathStack = savedState.pathStack || [];

            // Sync treeview active folder in localStorage before treeview initializes
            if (savedState.treeviewFolderId) {
                localStorage.setItem('treeviewActiveFolder', savedState.treeviewFolderId);
            }

            // Set flag to prevent switchViewMode from interfering
            folderRestorationInProgress = true;
        }

        // Skip renderFoldersView when we have a folder to restore
        const hasFolderToRestore = !!currentFolderId;
        loadFolders(hasFolderToRestore).then(() => {
            // After folders are loaded, restore folder state if exists
            if (currentFolderId) {
                openFolder(currentFolderId).then(() => {
                    // Clear restoration flag
                    folderRestorationInProgress = false;
                    // Restore scroll position after folder loads
                    if (savedState && savedState.scrollPosition > 0) {
                        setTimeout(() => {
                            window.scrollTo(0, savedState.scrollPosition);
                        }, 150);
                    }
                }).catch(() => {
                    // Clear restoration flag
                    folderRestorationInProgress = false;
                    // If folder open fails, go to root
                    renderFoldersView(1, true);
                });
            } else {
                // Clear restoration flag
                folderRestorationInProgress = false;
                // No folder to restore, show the folders view
                renderFoldersView(1, true);
            }
        });
    }
    
    // Render initial page based on view mode
    switchViewMode(currentViewMode, false);
    
    // Initialize sort button states if sort is active
    if (paginationState.sortBy && paginationState.sortOrder) {
        const activeBtn = document.querySelector(`[data-sort="${paginationState.sortBy}"][data-order="${paginationState.sortOrder}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    }
    
    // Initialize card size
    initCardSize();
    initCardSizeCards();
}

// Switch view mode
function switchViewMode(mode, saveToStorage = true) {
    currentViewMode = mode;

    if (saveToStorage) {
        localStorage.setItem('patientsViewMode', mode);
    }

    // Update unified filter manager view
    if (unifiedFilterManager) {
        unifiedFilterManager.setView(mode);
    }

    // Update toggle buttons (unified filter bar toggle - desktop and mobile)
    document.querySelectorAll('#viewModeToggleUnified button, #viewModeToggleMobile button').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === mode) {
            btn.classList.add('active');
        }
    });

    // Hide all views
    document.getElementById('patientsTableCard').style.display = 'none';
    document.getElementById('patientsCardsCard').style.display = 'none';
    document.getElementById('patientsFoldersCard').style.display = 'none';

    // Show selected view
    switch(mode) {
        case 'table':
            document.getElementById('patientsTableCard').style.display = 'block';
            renderPatientsTable();
            updatePaginationInfo();
            renderPaginationNav();
            stopFoldersAutoRefresh();
            break;
        case 'cards':
            document.getElementById('patientsCardsCard').style.display = 'block';
            renderPatientsCards();
            updatePaginationInfoCards();
            renderPaginationNavCards();
            stopFoldersAutoRefresh();
            break;
        case 'folders':
            document.getElementById('patientsFoldersCard').style.display = 'block';
            // Skip loading/rendering if folder restoration is in progress
            // This prevents race condition where switchViewMode would reset to root view
            if (!folderRestorationInProgress) {
                if (foldersData.length === 0) {
                    loadFolders();
                } else {
                    renderFoldersView();
                }
            }
            // Start auto-refresh for folders
            startFoldersAutoRefresh();
            break;
    }
}

// Render patients table with current page data
function renderPatientsTable() {
    const tableBody = document.getElementById('patientsTableBody');
    const { currentPage, itemsPerPage, filteredPatients } = paginationState;
    
    // Add loading state
    tableBody.parentElement.classList.add('table-loading');
    
    // Calculate pagination
    let startIndex, endIndex, patientsToShow;
    
    if (itemsPerPage === 'all') {
        startIndex = 0;
        endIndex = filteredPatients.length;
        patientsToShow = filteredPatients;
    } else {
        startIndex = (currentPage - 1) * itemsPerPage;
        endIndex = Math.min(startIndex + itemsPerPage, filteredPatients.length);
        patientsToShow = filteredPatients.slice(startIndex, endIndex);
    }
    
    // Clear table
    tableBody.innerHTML = '';
    
    // Add delay for smooth transition
    setTimeout(() => {
        if (patientsToShow.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2 mb-0">No patients to display</p>
                    </td>
                </tr>
            `;
        } else {
            let html = '';
            
            patientsToShow.forEach(patient => {
                const age = patient.dob ? calculateAge(patient.dob) : 'Not specified';
                const lastVisit = patient.last_visit ? formatDate(patient.last_visit) : 'Not visited yet';
                
                // Handle Arabic and English names properly
                const firstName = patient.first_name || '';
                const lastName = patient.last_name || '';
                const fullName = `${firstName} ${lastName}`.trim();
                
                // Get avatar initials and gender-based styling
                const firstChar = firstName.charAt(0).toUpperCase();
                const lastChar = lastName.charAt(0).toUpperCase();
                const avatarInitials = firstChar && lastChar ? `${firstChar}.${lastChar}` : '?.?';
                
                // Gender-based avatar color
                const avatarClass = patient.gender === 'Female' ? 'avatar-circle avatar-female me-3' : 'avatar-circle avatar-male me-3';
                
                // Get doctor who created this patient
                let doctorInfo = 'Unknown';
                if (patient.created_by_doctor_name) {
                    doctorInfo = patient.created_by_doctor_name;
                } else if (patient.created_by_name) {
                    doctorInfo = patient.created_by_name;
                }
                
                // Get color marker (will be fetched and cached)
                const colorMarker = patient.color_marker || null;
                
                html += `
                    <tr data-patient-id="${patient.id}">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="patient-info-marker-container" style="flex-shrink: 0; width: 12px; height: 12px; margin-right: 8px;">
                                    ${colorMarker ? `
                                        <div class="patient-color-marker-table me-2" 
                                             style="width: 12px; height: 12px; border-radius: 50%; background: ${colorMarker}; cursor: pointer; flex-shrink: 0;"
                                             onclick="event.stopPropagation(); showColorMarkerModal(${patient.id}, '${colorMarker}')"
                                             title="Click to change color marker"></div>
                                    ` : `
                                        <button class="btn btn-sm btn-link p-0 me-2 patient-color-marker-add-table" 
                                                style="width: 12px; height: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; padding: 0; opacity: 0; transition: opacity 0.2s ease;"
                                                onmouseenter="this.style.opacity='1'"
                                                onmouseleave="this.style.opacity='0'"
                                                onclick="event.stopPropagation(); showColorMarkerModal(${patient.id}, null)"
                                                title="Click to add color marker">
                                            <i class="bi bi-plus-lg" style="font-size: 0.6rem;"></i>
                                        </button>
                                    `}
                                </div>
                                <div class="${avatarClass}">
                                    ${avatarInitials}
                                </div>
                                <div>
                                    <h6 class="mb-1">
                                        <a href="/doctor/patients/${patient.id}" 
                                           class="patient-name-link" 
                                           style="text-decoration: none; color: var(--accent) !important; font-weight: 600; transition: all 0.2s ease;" 
                                           onmouseover="this.style.color='var(--text) !important'; this.style.fontWeight='800 !important'; this.style.textDecoration='none';" 
                                           onmouseout="this.style.color='var(--accent) !important'; this.style.fontWeight='600 !important'; this.style.textDecoration='none';">
                                            ${escapeHtml(fullName)}
                                        </a>
                                    </h6>
                                    <small class="text-muted">ID: #${patient.id}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            ${patient.phone ? `
                                <div class="phone-number-container" style="position: relative; display: inline-block;">
                                    <a href="tel:${escapeHtml(patient.phone)}" 
                                       class="phone-number-link" 
                                       style="text-decoration: none; color: var(--accent); font-weight: 500; cursor: pointer; transition: all 0.2s ease;">
                                        <i class="bi bi-telephone me-1"></i>
                                        ${escapeHtml(patient.phone)}
                                    </a>
                                    <span class="phone-htooltip">
                                        <div class="phone-actions">
                                            <a href="tel:${escapeHtml(patient.phone)}" class="phone-action-btn" title="Call">
                                                <i class="bi bi-telephone-fill"></i>
                                                <span>Call</span>
                                            </a>
                                            <a href="https://wa.me/+2${escapeHtml(patient.phone).replace(/[^0-9]/g, '')}" target="_blank" class="phone-action-btn whatsapp-btn" title="WhatsApp">
                                                <i class="bi bi-whatsapp"></i>
                                                <span>WhatsApp</span>
                                            </a>
                                        </div>
                                    </span>
                                </div>
                            ` : '<span class="text-muted">Not available</span>'}
                            ${patient.alt_phone ? `
                                <div class="phone-number-container mt-1" style="position: relative; display: inline-block;">
                                    <a href="tel:${escapeHtml(patient.alt_phone)}" 
                                       class="phone-number-link" 
                                       style="text-decoration: none; color: var(--accent); font-weight: 500; cursor: pointer; transition: all 0.2s ease;">
                                        <i class="bi bi-telephone-plus me-1"></i>
                                        <small>${escapeHtml(patient.alt_phone)}</small>
                                    </a>
                                    <span class="phone-htooltip">
                                        <div class="phone-actions">
                                            <a href="tel:${escapeHtml(patient.alt_phone)}" class="phone-action-btn" title="Call">
                                                <i class="bi bi-telephone-fill"></i>
                                                <span>Call</span>
                                            </a>
                                            <a href="https://wa.me/+2${escapeHtml(patient.alt_phone).replace(/[^0-9]/g, '')}" target="_blank" class="phone-action-btn whatsapp-btn" title="WhatsApp">
                                                <i class="bi bi-whatsapp"></i>
                                                <span>WhatsApp</span>
                                            </a>
                                        </div>
                                    </span>
                                </div>
                            ` : ''}
                        </td>
                        <td>
                            ${patient.gender ? `
                                <span class="badge ${patient.gender === 'Female' ? 'bg-pink' : 'bg-primary'}" style="font-size: 0.875rem; padding: 0.4rem 0.6rem;">
                                    <i class="bi ${patient.gender === 'Female' ? 'bi-gender-female' : 'bi-gender-male'} me-1"></i>
                                    ${escapeHtml(patient.gender)}
                                </span>
                            ` : '<span class="text-muted">Not specified</span>'}
                        </td>
                        <td>
                            ${age !== 'Not specified' ? `${age} years` : '<span class="text-muted">Not specified</span>'}
                        </td>
                        <td>
                            <div class="doctor-info">
                                ${doctorInfo === 'Unknown' ? 
                                    '<span class="badge bg-secondary">Unknown</span>' :
                                    `<span class="badge bg-info">${escapeHtml(doctorInfo)}</span>`
                                }
                            </div>
                        </td>
                        <td>
                            ${patient.last_visit ?
                                `<span class="badge bg-success">${lastVisit}</span>` :
                                '<span class="badge bg-secondary">Not visited yet</span>'
                            }
                        </td>
                        <td>
                            ${renderClinicBadge(patient)}
                        </td>
                        <td>
                            <span class="badge bg-primary">${patient.total_appointments || 0}</span>
                        </td>
                        <td>
                            <div id="patientTagsTable_${patient.id}" style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center;">
                                <!-- Tags will be loaded here -->
                            </div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="/doctor/patients/${patient.id}" 
                                   class="btn btn-sm btn-outline-warning" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   data-bs-title="View patient details and medical history">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-info" 
                                        onclick="editPatient(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Edit patient information and details">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" 
                                        onclick="bookAppointment(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Book a new appointment for this patient">
                                    <i class="bi bi-calendar-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="deletePatient(${patient.id}, '${escapeHtml(fullName).replace(/'/g, '\\\'')}')" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Delete the patient from the system (cannot be undone)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            tableBody.innerHTML = html;
            
            // Add hover effects for add marker buttons
            tableBody.querySelectorAll('tr').forEach(row => {
                const addBtn = row.querySelector('.patient-color-marker-add-table');
                if (addBtn) {
                    row.addEventListener('mouseenter', function() {
                        const btn = this.querySelector('.patient-color-marker-add-table');
                        if (btn) {
                            btn.style.opacity = '1';
                        }
                    });
                    
                    row.addEventListener('mouseleave', function() {
                        const btn = this.querySelector('.patient-color-marker-add-table');
                        if (btn) {
                            btn.style.opacity = '0';
                        }
                    });
                }
            });
            
            // Refresh tooltips for new content
            setTimeout(() => {
                refreshTooltips();
            }, 100);
            
            // Fetch color markers and tags for table patients
            fetchColorMarkersForTablePatients(patientsToShow);
            fetchTagsForTablePatients(patientsToShow);
        }
        
        // Remove loading state
        tableBody.parentElement.classList.remove('table-loading');
        
    }, 150); // Short delay for smooth transition
}

// Update pagination information
function updatePaginationInfo() {
    const { currentPage, itemsPerPage, filteredPatients } = paginationState;
    
    document.getElementById('totalPatientsCount').textContent = filteredPatients.length;
    document.getElementById('totalPatients').textContent = filteredPatients.length;
    
    if (itemsPerPage === 'all') {
        document.getElementById('showingFrom').textContent = filteredPatients.length > 0 ? '1' : '0';
        document.getElementById('showingTo').textContent = filteredPatients.length;
        
        // Hide pagination nav when showing all
        document.getElementById('paginationContainer').style.display = 'block';
        document.getElementById('paginationNav').style.display = 'none';
    } else {
        const startIndex = (currentPage - 1) * itemsPerPage + 1;
        const endIndex = Math.min(currentPage * itemsPerPage, filteredPatients.length);
        
        document.getElementById('showingFrom').textContent = filteredPatients.length > 0 ? startIndex : '0';
        document.getElementById('showingTo').textContent = endIndex;
        
        // Show pagination nav
        document.getElementById('paginationNav').style.display = 'flex';
    }
}

// Render pagination navigation
function renderPaginationNav() {
    const paginationNav = document.getElementById('paginationNav');
    const { currentPage, itemsPerPage, filteredPatients } = paginationState;
    
    if (itemsPerPage === 'all') {
        paginationNav.innerHTML = '';
        return;
    }
    
    const totalPages = Math.ceil(filteredPatients.length / itemsPerPage);
    
    if (totalPages <= 1) {
        paginationNav.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})" aria-label="Previous">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;
    
    // Page numbers with smart pagination
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);
    
    // Adjust if we're near the beginning or end
    if (currentPage <= 3) {
        startPage = 1;
        endPage = Math.min(5, totalPages);
    } else if (currentPage >= totalPages - 2) {
        startPage = Math.max(1, totalPages - 4);
        endPage = totalPages;
    }
    
    // First page and ellipsis
    if (startPage > 1) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changePage(1)">1</a>
            </li>
        `;
        if (startPage > 2) {
            html += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
        }
    }
    
    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>
        `;
    }
    
    // Last page and ellipsis
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
        }
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a>
            </li>
        `;
    }
    
    // Next button
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})" aria-label="Next">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;
    
    paginationNav.innerHTML = html;
}

// Change page function
function changePage(page) {
    const { itemsPerPage, filteredPatients } = paginationState;
    
    if (itemsPerPage === 'all') return;
    
    const totalPages = Math.ceil(filteredPatients.length / itemsPerPage);
    
    if (page < 1 || page > totalPages) return;
    
    paginationState.currentPage = page;
    
    renderPatientsTable();
    updatePaginationInfo();
    renderPaginationNav();
    
    // Smooth scroll to table top
    document.querySelector('.card').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
    });
}

// Change items per page
function changeItemsPerPage(newLimit) {
    paginationState.itemsPerPage = newLimit === 'all' ? 'all' : parseInt(newLimit);
    paginationState.currentPage = 1; // Reset to first page
    
    renderPatientsTable();
    updatePaginationInfo();
    renderPaginationNav();
    
}

// Escape HTML function
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
}

// Filter patients by doctor
function filterByDoctor(doctorId) {
    
    // Update active button
    document.querySelectorAll('#doctorFilterGroup .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-doctor="${doctorId}"]`).classList.add('active');
    
    // Update current filter
    paginationState.currentDoctorFilter = doctorId;
    
    // Update filter text
    let filterText = 'All Doctors';
    if (doctorId !== 'all') {
        const doctor = paginationState.doctors.find(d => d.id == doctorId);
        if (doctor) {
            filterText = doctor.display_name;
        }
    }
    document.getElementById('currentFilterText').textContent = filterText;
    
    // Apply doctor filter
    applyDoctorFilter();
    
    // Apply current search filter if exists
    const quickSearch = document.getElementById('quickSearch');
    if (quickSearch && quickSearch.value.trim()) {
        filterPatientsLocally(quickSearch.value);
    } else {
        // Update display
        renderPatientsTable();
        updatePaginationInfo();
        renderPaginationNav();
    }
    
    // Update clear filters button visibility
    updateClearFiltersVisibility();
}

// Apply doctor filter to patients
function applyDoctorFilter() {
    const { currentDoctorFilter, currentGenderFilter, currentAgeFilter, currentLastVisitFilter, allPatients } = paginationState;
    
    let filtered = [...allPatients];
    
    // Apply doctor filter
    if (currentDoctorFilter !== 'all') {
        filtered = filtered.filter(patient => {
            return patient.created_by_doctor_id == currentDoctorFilter;
        });
    }
    
    // Apply gender filter
    if (currentGenderFilter) {
        filtered = filtered.filter(patient => {
            return patient.gender === currentGenderFilter;
        });
    }
    
    // Apply age filter
    if (currentAgeFilter.min !== null || currentAgeFilter.max !== null) {
        filtered = filtered.filter(patient => {
            if (!patient.dob) return false;
            const age = calculateAge(patient.dob);
            
            if (currentAgeFilter.min !== null && age < currentAgeFilter.min) {
                return false;
            }
            if (currentAgeFilter.max !== null && age > currentAgeFilter.max) {
                return false;
            }
            return true;
        });
    }
    
    // Apply last visit filter
    if (currentLastVisitFilter.from !== null || currentLastVisitFilter.to !== null) {
        filtered = filtered.filter(patient => {
            if (!patient.last_visit) {
                // If filtering by date range and patient has no visit, exclude them
                return false;
            }
            
            const visitDate = new Date(patient.last_visit);
            visitDate.setHours(0, 0, 0, 0);
            
            if (currentLastVisitFilter.from !== null) {
                const fromDate = new Date(currentLastVisitFilter.from);
                fromDate.setHours(0, 0, 0, 0);
                if (visitDate < fromDate) {
                    return false;
                }
            }
            
            if (currentLastVisitFilter.to !== null) {
                const toDate = new Date(currentLastVisitFilter.to);
                toDate.setHours(23, 59, 59, 999);
                if (visitDate > toDate) {
                    return false;
                }
            }
            
            return true;
        });
    }
    
    // Removed: nameFilter logic - filter by name feature removed
    
    paginationState.filteredPatients = filtered;
    paginationState.totalItems = filtered.length;
    
    // Reset to first page
    paginationState.currentPage = 1;
}

// Apply gender filter
function applyGenderFilter(gender) {
    paginationState.currentGenderFilter = gender;
    
    // Update filter button appearance
    const filterBtn = document.querySelector('.gender-filter-btn');
    if (filterBtn) {
        if (gender) {
            filterBtn.classList.add('active');
            filterBtn.style.color = 'var(--accent)';
        } else {
            filterBtn.classList.remove('active');
            filterBtn.style.color = 'var(--accent)';
        }
    }
    
    // Apply gender filter
    applyDoctorFilter();
    
    // Apply current search filter if exists
    const quickSearch = document.getElementById('quickSearch');
    if (quickSearch && quickSearch.value.trim()) {
        filterPatientsLocally(quickSearch.value);
    } else {
        // Update display
        renderPatientsTable();
        updatePaginationInfo();
        renderPaginationNav();
    }
    
    // Update clear filters button visibility
    updateClearFiltersVisibility();
}

// Update clear filters and sorting buttons visibility
function updateClearFiltersVisibility() {
    const clearFiltersGroup = document.getElementById('clearFiltersGroup');
    const clearSortingBtn = document.getElementById('clearSortingBtn');
    
    if (!clearFiltersGroup) return;
    
    // Check if any filter is active
    const hasActiveFilter = 
        paginationState.currentDoctorFilter !== 'all' ||
        paginationState.currentGenderFilter !== null ||
        (paginationState.currentAgeFilter.min !== null || paginationState.currentAgeFilter.max !== null) ||
        (paginationState.currentLastVisitFilter.from !== null || paginationState.currentLastVisitFilter.to !== null);
    
    // Check if sorting is active
    const hasActiveSort = paginationState.sortBy !== null && paginationState.sortOrder !== null;
    
    // Show/hide clear filters button
    if (hasActiveFilter || hasActiveSort) {
        clearFiltersGroup.classList.remove('d-none');
        clearFiltersGroup.style.display = '';
    } else {
        clearFiltersGroup.classList.add('d-none');
    }
    
    // Show/hide clear sorting button
    if (hasActiveSort) {
        if (clearSortingBtn) {
            clearSortingBtn.classList.remove('d-none');
        }
    } else {
        if (clearSortingBtn) {
            clearSortingBtn.classList.add('d-none');
        }
    }
}

// Clear all filters
function clearAllFilters() {
    // Reset all filters
    paginationState.currentDoctorFilter = 'all';
    paginationState.currentGenderFilter = null;
    paginationState.currentAgeFilter = { min: null, max: null };
    paginationState.currentLastVisitFilter = { from: null, to: null };
    
    // Update filter buttons appearance
    const genderFilterBtn = document.querySelector('.gender-filter-btn');
    if (genderFilterBtn) {
        genderFilterBtn.classList.remove('active');
    }
    
    const ageFilterBtn = document.querySelector('.age-filter-btn');
    if (ageFilterBtn) {
        ageFilterBtn.classList.remove('active');
    }
    
    const lastVisitFilterBtn = document.querySelector('.last-visit-filter-btn');
    if (lastVisitFilterBtn) {
        lastVisitFilterBtn.classList.remove('active');
    }
    
    // Update doctor filter buttons
    document.querySelectorAll('#doctorFilterGroup button').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.doctor === 'all') {
            btn.classList.add('active');
        }
    });
    
    // Apply filters (will reset to all)
    applyDoctorFilter();
    
    // Apply current search filter if exists
    const quickSearch = document.getElementById('quickSearch');
    if (quickSearch && quickSearch.value.trim()) {
        filterPatientsLocally(quickSearch.value);
    } else {
        // Update display
        renderPatientsTable();
        updatePaginationInfo();
        renderPaginationNav();
    }
    
    // Update visibility
    updateClearFiltersVisibility();
}

// Clear sorting
function clearSorting() {
    // Reset sorting
    paginationState.sortBy = null;
    paginationState.sortOrder = null;
    
    // Remove active class from all sort buttons
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Reload data without sorting
    fetch('/api/patients', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.patients) {
            paginationState.allPatients = data.patients;
            
            // Reapply current filters
            applyDoctorFilter();
            
            // Reapply quick search if exists
            const quickSearch = document.getElementById('quickSearch');
            if (quickSearch && quickSearch.value.trim()) {
                filterPatientsLocally(quickSearch.value);
            } else {
                // Just re-render with current filters
                renderPatientsTable();
                updatePaginationInfo();
                renderPaginationNav();
            }
            
            // Update statistics
            updateStatistics(data.patients);
        }
    })
    .catch(error => {
        console.error('Error loading patients:', error);
    });
    
    // Update visibility
    updateClearFiltersVisibility();
}

// Filter patients by name
// Removed: filterByName function - filter by name feature removed

// Clear patient name filter
// Removed: clearPatientNameFilter function - filter by name feature removed

// Filter patients locally (for main table pagination)
function filterPatientsLocally(query) {
    // First apply doctor filter
    applyDoctorFilter();
    
    if (!query || query.trim().length < 2) {
        // No search query, just use doctor filter results
    } else {
        const searchTerm = query.trim().toLowerCase();
        paginationState.filteredPatients = paginationState.filteredPatients.filter(patient => {
            const fullName = `${patient.first_name} ${patient.last_name}`.toLowerCase();
            const phone = (patient.phone || '').toLowerCase();
            const altPhone = (patient.alt_phone || '').toLowerCase();
            const nationalId = (patient.national_id || '').toLowerCase();
            
            return fullName.includes(searchTerm) || 
                   phone.includes(searchTerm) || 
                   altPhone.includes(searchTerm) || 
                   nationalId.includes(searchTerm);
        });
    }
    
    // Reset to first page after filtering
    paginationState.currentPage = 1;
    
    // Update display based on current view mode
    if (currentViewMode === 'table') {
        renderPatientsTable();
        updatePaginationInfo();
        renderPaginationNav();
    } else if (currentViewMode === 'cards') {
        initCardSizeCards(); // Initialize card size for cards view
        renderPatientsCards();
        updatePaginationInfoCards();
        renderPaginationNavCards();
    }
}

// Search patients function (for modal)
function searchPatients(query) {
    const searchLoading = document.getElementById('searchLoading');
    const searchInitial = document.getElementById('searchInitial');
    const noResults = document.getElementById('noResults');
    const searchResultsList = document.getElementById('searchResultsList');
    
    // Hide all states
    searchInitial.style.display = 'none';
    noResults.style.display = 'none';
    searchResultsList.style.display = 'none';
    
    if (!query || query.trim().length < 2) {
        searchInitial.style.display = 'block';
        return;
    }
    
    // Show loading
    searchLoading.style.display = 'block';
    
    // Cancel previous request
    if (currentSearchRequest) {
        currentSearchRequest.abort();
    }
    
    // Create new request
    currentSearchRequest = new AbortController();
    
    fetch(`/api/patients/search?q=${encodeURIComponent(query.trim())}`, {
        signal: currentSearchRequest.signal
    })
    .then(response => response.json())
    .then(data => {
        searchLoading.style.display = 'none';
        
        if (data.ok && data.data && data.data.length > 0) {
            displaySearchResults(data.data, query.trim());
        } else {
            noResults.style.display = 'block';
        }
    })
    .catch(error => {
        searchLoading.style.display = 'none';
        if (error.name !== 'AbortError') {
            console.error('Search error:', error);
            noResults.style.display = 'block';
        }
    });
}

// Display search results
function displaySearchResults(patients, searchTerm) {
    const searchResultsList = document.getElementById('searchResultsList');
    let html = '';
    
    patients.forEach(patient => {
        const fullName = `${patient.first_name} ${patient.last_name}`;
        const age = patient.dob ? calculateAge(patient.dob) : 'N/A';
        const lastVisit = patient.last_visit ? formatDate(patient.last_visit) : 'Never';
        
        // Highlight search terms
        const highlightedName = highlightSearchTerm(fullName, searchTerm);
        const highlightedPhone = highlightSearchTerm(patient.phone || '', searchTerm);
        const highlightedNationalId = highlightSearchTerm(patient.national_id || '', searchTerm);
        
        html += `
            <div class="search-result-item" onclick="selectSearchResult(${patient.id})">
                <div class="d-flex align-items-center">
                                                        <div class="search-result-avatar ${patient.gender === 'Female' ? 'avatar-female' : 'avatar-male'} me-3">
                                        ${getAvatarInitials(patient.first_name, patient.last_name)}
                                    </div>
                    <div class="search-result-info flex-grow-1">
                        <h6 class="mb-1">${highlightedName}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    <i class="bi bi-telephone me-1"></i>
                                    ${highlightedPhone || 'No phone'}
                                </small>
                                ${patient.alt_phone ? `<small class="text-muted d-block">
                                    <i class="bi bi-telephone-plus me-1"></i>
                                    ${patient.alt_phone}
                                </small>` : ''}
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    <i class="bi bi-person me-1"></i>
                                    Age: ${age} years
                                </small>
                                ${patient.national_id ? `<small class="text-muted d-block">
                                    <i class="bi bi-card-text me-1"></i>
                                    ID: ${highlightedNationalId}
                                </small>` : ''}
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-primary me-2">${patient.total_appointments || 0} visits</span>
                            <span class="badge bg-success">Last: ${lastVisit}</span>
                        </div>
                    </div>
                    <div class="search-result-actions ms-3">
                        <div class="btn-group-vertical">
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="event.stopPropagation(); viewPatient(${patient.id})"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="View full patient details and medical history">
                                <i class="bi bi-eye me-1"></i>View
                            </button>
                            <button class="btn btn-sm btn-outline-success" 
                                    onclick="event.stopPropagation(); bookAppointment(${patient.id})"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="Book a new appointment for this patient">
                                <i class="bi bi-calendar-plus me-1"></i>Book
                            </button>
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="event.stopPropagation(); deletePatient(${patient.id}, '${patient.first_name} ${patient.last_name}')"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="Delete patient permanently from the system (cannot be undone)">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    searchResultsList.innerHTML = html;
    searchResultsList.style.display = 'block';
}

// Select search result
function selectSearchResult(patientId) {
    viewPatient(patientId);
}

// Highlight search terms
function highlightSearchTerm(text, searchTerm) {
    if (!text || !searchTerm) return text;
    
    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return text.replace(regex, '<span class="search-highlight">$1</span>');
}

// Get avatar initials with proper UTF-8 support for Arabic names
function getAvatarInitials(firstName, lastName) {
    if (!firstName || !lastName) {
        return '?.?';
    }
    
    // Get first character of each name using proper Unicode handling
    const firstChar = firstName.charAt(0).toUpperCase();
    const lastChar = lastName.charAt(0).toUpperCase();
    
    return firstChar + '.' + lastChar;
}

// Calculate age
function calculateAge(dob) {
    const today = new Date();
    const birthDate = new Date(dob);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Initialize search functionality  
document.addEventListener('DOMContentLoaded', function() {
    // Check if we need to open add patient modal
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('openModal') === 'addPatient') {
        setTimeout(() => {
            const addPatientBtn = document.querySelector('[data-bs-target="#addPatientModal"]');
            if (addPatientBtn) {
                addPatientBtn.click();
            }
            // Clean URL
            const newUrl = window.location.pathname + window.location.search.replace(/[?&]openModal=addPatient/, '').replace(/^&/, '?');
            window.history.replaceState({}, '', newUrl);
        }, 500);
    }
    
    // Folder state restoration is now handled in initializePagination()
    // using the new restoreFolderNavigationState() function

    // Initialize pagination (handles folder state restoration)
    initializePagination();

    // Save folder navigation state when leaving page
    window.addEventListener('beforeunload', () => {
        if (currentViewMode === 'folders' && currentFolderId) {
            saveFolderNavigationState();
        }
    });

    // Save state on visibility change (mobile tab switching, app backgrounding)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && currentViewMode === 'folders' && currentFolderId) {
            saveFolderNavigationState();
        }
    });

    // Setup pagination limit selector
    // Handle pagination limit change (now using custom select)
    const paginationLimitSelect = document.getElementById('paginationLimit');
    if (paginationLimitSelect) {
        paginationLimitSelect.addEventListener('change', function() {
            changeItemsPerPage(this.value);
        });
        
        // Also listen for custom select changes
        const paginationLimitField = paginationLimitSelect.closest('.field.menu');
        if (paginationLimitField) {
            const paginationLimitButton = paginationLimitField.querySelector('.custom-select-toggle');
            if (paginationLimitButton) {
                paginationLimitButton.addEventListener('click', function() {
                    // Ensure custom select is initialized
                    setTimeout(() => {
                        initCustomSelects();
                    }, 50);
                });
            }
        }
    }
    
    // Setup quick search
    const quickSearch = document.getElementById('quickSearch');
    const clearQuickSearch = document.getElementById('clearQuickSearch');
    
    if (quickSearch) {
        // Debounced search for main table
        const debouncedQuickSearch = debounce(filterPatientsLocally, 300);
        
        quickSearch.addEventListener('input', function() {
            const hasValue = this.value.trim().length > 0;
            // Show/hide clear button based on input value
            if (clearQuickSearch) {
                clearQuickSearch.style.display = hasValue ? 'block' : 'none';
            }
            debouncedQuickSearch(this.value);
        });
        
        // Clear search
        if (clearQuickSearch) {
            clearQuickSearch.addEventListener('click', function() {
                quickSearch.value = '';
                this.style.display = 'none';
                filterPatientsLocally('');
                quickSearch.focus();
            });
        }
    }
    
    // Quick search for cards view
    const quickSearchCards = document.getElementById('quickSearchCards');
    const clearQuickSearchCards = document.getElementById('clearQuickSearchCards');
    
    if (quickSearchCards) {
        const debouncedQuickSearchCards = debounce(filterPatientsLocally, 300);
        
        quickSearchCards.addEventListener('input', function() {
            const hasValue = this.value.trim().length > 0;
            if (clearQuickSearchCards) {
                clearQuickSearchCards.style.display = hasValue ? 'block' : 'none';
            }
            debouncedQuickSearchCards(this.value);
        });
        
        if (clearQuickSearchCards) {
            clearQuickSearchCards.addEventListener('click', function() {
                quickSearchCards.value = '';
                this.style.display = 'none';
                filterPatientsLocally('');
                quickSearchCards.focus();
            });
        }
    }
    
    // Pagination limit for cards
    const paginationLimitCards = document.getElementById('paginationLimitCards');
    if (paginationLimitCards) {
        paginationLimitCards.addEventListener('change', function() {
            paginationState.currentPage = 1;
            if (currentViewMode === 'cards') {
                renderPatientsCards();
                updatePaginationInfoCards();
                renderPaginationNavCards();
            }
        });
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        const isModalOpen = document.querySelector('.modal.show');
        const isInputFocused = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || 
                             e.target.contentEditable === 'true';
        
        // Quick search shortcut (Ctrl+F when not in modal)
        if ((e.ctrlKey || e.metaKey) && e.key && e.key.toLowerCase() === 'f' && !isModalOpen) {
            e.preventDefault();
            const activeQuickSearch = document.getElementById('quickSearch') || document.getElementById('quickSearchCards');
            if (activeQuickSearch) {
                activeQuickSearch.focus();
                activeQuickSearch.select();
            }
            return;
        }
        
        // Pagination shortcuts (only when not typing in inputs and no modal is open)
        if (!isInputFocused && !isModalOpen && paginationState.itemsPerPage !== 'all') {
            const totalPages = Math.ceil(paginationState.filteredPatients.length / paginationState.itemsPerPage);
            
            switch(e.key) {
                case 'ArrowLeft':
                case 'ArrowRight':
                    e.preventDefault();
                    if (e.key === 'ArrowLeft' && paginationState.currentPage < totalPages) {
                        changePage(paginationState.currentPage + 1);
                    } else if (e.key === 'ArrowRight' && paginationState.currentPage > 1) {
                        changePage(paginationState.currentPage - 1);
                    }
                    break;
                    
                case 'Home':
                    e.preventDefault();
                    changePage(1);
                    break;
                    
                case 'End':
                    e.preventDefault();
                    changePage(totalPages);
                    break;
            }
        }
    });
    
    const globalSearch = document.getElementById('globalSearch');
    const clearSearch = document.getElementById('clearSearch');
    const searchModal = document.getElementById('searchModal');
    const searchButton = document.querySelector('[data-bs-target="#searchModal"]');
    
    // Check if elements exist (only available on patients.php page)
    if (!globalSearch || !clearSearch) {
        return; // Exit early if not on patients page
    }
    
    // Debounced search
    const debouncedSearch = debounce(searchPatients, 300);
    
    // Search input event
    globalSearch.addEventListener('input', function() {
        debouncedSearch(this.value);
    });
    
    // Clear search
    clearSearch.addEventListener('click', function() {
        globalSearch.value = '';
        globalSearch.focus();
        document.getElementById('searchInitial').style.display = 'block';
        document.getElementById('searchLoading').style.display = 'none';
        document.getElementById('noResults').style.display = 'none';
        document.getElementById('searchResultsList').style.display = 'none';
    });
    
    // Focus search input when modal opens
    searchModal.addEventListener('shown.bs.modal', function() {
        globalSearch.focus();
    });
    
    // Reset search when modal closes
    searchModal.addEventListener('hidden.bs.modal', function() {
        globalSearch.value = '';
        document.getElementById('searchInitial').style.display = 'block';
        document.getElementById('searchLoading').style.display = 'none';
        document.getElementById('noResults').style.display = 'none';
        document.getElementById('searchResultsList').style.display = 'none';
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Skip if key is undefined
        if (!e.key) {
            return;
        }
        
        // Open search modal with 'F' key or Arabic 'ب' key (only if no input is focused)
        // Also support Arabic keyboard layout alternatives
        const searchKeys = ['f', 'ب']; // F key and Arabic 'ba' (same position on keyboard)
        const isSearchKey = searchKeys.includes(e.key.toLowerCase()) || searchKeys.includes(e.key);
        
        if (isSearchKey && !isInputFocused() && !searchModal.classList.contains('show')) {
            e.preventDefault();
            searchButton.click();
        }
        
        // Open add patient modal with 'Ctrl+N' or 'N' key or Arabic 'ى' key
        const addPatientKeys = ['n', 'ى']; // N key and Arabic 'ya' (same position on keyboard)
        const isAddPatientKey = e.key && (addPatientKeys.includes(e.key.toLowerCase()) || addPatientKeys.includes(e.key));
        const isCtrlN = (e.ctrlKey || e.metaKey) && e.key && e.key.toLowerCase() === 'n';
        
        if ((isAddPatientKey || isCtrlN) && !isInputFocused() && !document.querySelector('.modal.show')) {
            e.preventDefault();
            document.querySelector('[data-bs-target="#addPatientModal"]').click();
        }
        
        // Close modals with 'Escape' key
        if (e.key === 'Escape') {
            if (searchModal.classList.contains('show')) {
                e.preventDefault();
                bootstrap.Modal.getInstance(searchModal).hide();
            } else if (document.getElementById('addPatientModal').classList.contains('show')) {
                e.preventDefault();
                bootstrap.Modal.getInstance(document.getElementById('addPatientModal')).hide();
            } else if (document.getElementById('deletePatientModal').classList.contains('show')) {
                e.preventDefault();
                bootstrap.Modal.getInstance(document.getElementById('deletePatientModal')).hide();
            } else if (document.getElementById('deletePatientConfirmModal').classList.contains('show')) {
                e.preventDefault();
                bootstrap.Modal.getInstance(document.getElementById('deletePatientConfirmModal')).hide();
            }
        }
        
        // Focus search input with 'Ctrl+F' or 'Cmd+F' when modal is open
        // Also support Arabic layout
        if ((e.ctrlKey || e.metaKey) && e.key && (e.key.toLowerCase() === 'f' || e.key === 'ب') && searchModal.classList.contains('show')) {
            e.preventDefault();
            globalSearch.focus();
            globalSearch.select();
        }
        
        // Save patient with 'Ctrl+S' when add patient modal is open
        if ((e.ctrlKey || e.metaKey) && e.key && e.key.toLowerCase() === 's' && document.getElementById('addPatientModal').classList.contains('show')) {
            e.preventDefault();
            const submitButton = document.getElementById('addPatientSubmit');
            if (!submitButton.disabled) {
                submitButton.click();
            }
        }
    });
    
    // Helper function to check if any input is currently focused
    function isInputFocused() {
        const activeElement = document.activeElement;
        return activeElement && (
            activeElement.tagName === 'INPUT' || 
            activeElement.tagName === 'TEXTAREA' || 
            activeElement.tagName === 'SELECT' ||
            activeElement.contentEditable === 'true'
        );
    }
    
    // Initialize Add Patient Modal
    initializeAddPatientModal();
    
    // Initialize phone tooltip click handlers
    initializePhoneTooltips();
});

// Add Patient functionality
function initializeAddPatientModal() {
    const addPatientForm = document.getElementById('addPatientForm');
    const addPatientModal = document.getElementById('addPatientModal');
    const addPatientSubmit = document.getElementById('addPatientSubmit');
    const addPatientMessage = document.getElementById('addPatientMessage');
    
    // Reset form when modal opens
    addPatientModal.addEventListener('show.bs.modal', function() {
        addPatientForm.reset();
        addPatientForm.classList.remove('was-validated');
        hideMessage();
        resetSubmitButton();
        
        // Focus on first name field
        setTimeout(() => {
            document.getElementById('firstName').focus();
        }, 300);
    });
    
    // Handle form submission
    addPatientForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!addPatientForm.checkValidity()) {
            addPatientForm.classList.add('was-validated');
            showMessage('Please fill in all required fields correctly.', 'error');
            return;
        }
        
        // Additional validation
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const gender = document.getElementById('gender').value;
        
        if (!firstName || !lastName || !phone) {
            showMessage('First name, last name, and phone number are required.', 'error');
            return;
        }
        
        if (!gender) {
            showMessage('Please select the patient\'s gender.', 'error');
            document.getElementById('gender').focus();
            return;
        }

        const clinicSelect = document.getElementById('patientClinic');
        if (clinicSelect && !clinicSelect.value) {
            showMessage('Please select a clinic.', 'error');
            clinicSelect.focus();
            return;
        }

        // Validate phone number format (more flexible validation)
        const cleanPhone = phone.replace(/[\s\-\(\)]/g, ''); // Remove spaces, dashes, parentheses
        const phoneRegex = /^(\+\d{1,3})?\d{7,15}$/;
        if (!phoneRegex.test(cleanPhone)) {
            showMessage('Please enter a valid phone number (7-15 digits, optionally with country code).', 'error');
            return;
        }
        
        // Submit form
        submitPatientForm();
    });
    
    function submitPatientForm() {
        const formData = new FormData(addPatientForm);
        
        // Show loading state
        setSubmitButtonLoading(true);
        hideMessage();
        
        // Send AJAX request
        fetch('/api/patients', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            setSubmitButtonLoading(false);
            
            if (data.ok) {
                // Success
                showMessage('Patient added successfully!', 'success');
                
                // Reset form
                addPatientForm.reset();
                addPatientForm.classList.remove('was-validated');
                
                // Close modal after delay
                setTimeout(() => {
                    bootstrap.Modal.getInstance(addPatientModal).hide();
                    
                    // Refresh page to show new patient
                    window.location.reload();
                }, 1500);
                
            } else {
                // Error from server
                const errorMsg = data.error || data.message || 'Failed to add patient. Please try again.';
                showMessage(errorMsg, 'error');
                
                // Show validation errors if available
                if (data.details) {
                    showValidationErrors(data.details);
                }
            }
        })
        .catch(error => {
            setSubmitButtonLoading(false);
            console.error('Error adding patient:', error);
            showMessage('An error occurred while adding the patient. Please try again.', 'error');
        });
    }
    
    function showMessage(message, type) {
        addPatientMessage.className = `alert alert-${type === 'error' ? 'danger' : type}`;
        addPatientMessage.textContent = message;
        addPatientMessage.classList.remove('d-none');
    }
    
    function hideMessage() {
        addPatientMessage.classList.add('d-none');
    }
    
    function setSubmitButtonLoading(loading) {
        const btnText = addPatientSubmit.querySelector('.btn-text');
        const spinner = addPatientSubmit.querySelector('.spinner-border');
        
        if (loading) {
            addPatientSubmit.disabled = true;
            btnText.textContent = 'Adding...';
            spinner.classList.remove('d-none');
        } else {
            addPatientSubmit.disabled = false;
            btnText.textContent = 'Add Patient';
            spinner.classList.add('d-none');
        }
    }
    
    function resetSubmitButton() {
        setSubmitButtonLoading(false);
    }
    
    function showValidationErrors(errors) {
        // Clear previous validation errors
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
        });
        
        // Show new validation errors
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = errors[field];
                }
            }
        });
    }
    
    // Clear validation errors on input
    addPatientForm.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const feedback = e.target.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
            }
        }
    });
    
    // Age and Date of Birth conversion
    const dobInput = document.getElementById('dob');
    const ageInput = document.getElementById('age');
    
    // Convert age to date of birth
    ageInput.addEventListener('input', function() {
        const age = parseInt(this.value);
        if (age && age > 0 && age <= 150) {
            const today = new Date();
            const birthYear = today.getFullYear() - age;
            const birthDate = new Date(birthYear, today.getMonth(), today.getDate());
            dobInput.value = birthDate.toISOString().split('T')[0];
            
            // Clear age field after conversion to avoid confusion
            setTimeout(() => {
                this.value = '';
            }, 1000);
        }
    });
    
    // Convert date of birth to age (when user changes date)
    dobInput.addEventListener('change', function() {
        if (this.value) {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age >= 0 && age <= 150) {
                // Show calculated age in placeholder temporarily
                ageInput.placeholder = `Calculated age: ${age} years`;
                setTimeout(() => {
                    ageInput.placeholder = 'Enter age in years';
                }, 3000);
            }
        }
    });
}

// Delete Patient functionality - use window object for global scope
window.currentPatientToDelete = null;

function deletePatient(patientId, patientName) {
    
    window.currentPatientToDelete = {
        id: patientId,
        name: patientName
    };
    
    // Store in localStorage as backup
    localStorage.setItem('deletePatientData', JSON.stringify(window.currentPatientToDelete));
    
    
    // Set patient info in modal
    document.getElementById('deletePatientId').textContent = patientId;
    document.getElementById('deletePatientName').textContent = patientName;
    
    // Set avatar initials
    const nameParts = patientName.split(' ');
    let initials;
    if (nameParts.length >= 2) {
        initials = getAvatarInitials(nameParts[0], nameParts[1]);
    } else {
        // If only one name, use first two characters with dot
        const name = nameParts[0];
        const firstChar = name.charAt(0).toUpperCase();
        const secondChar = name.length > 1 ? name.charAt(1).toUpperCase() : '?';
        initials = firstChar + '.' + secondChar;
    }
    
    // Find patient gender for avatar color
    const patient = paginationState.allPatients.find(p => p.id == patientId);
    const avatarElement = document.getElementById('deletePatientAvatar');
    avatarElement.textContent = initials;
    
    // Apply gender-based class
    avatarElement.className = 'avatar-circle';
    if (patient && patient.gender === 'Female') {
        avatarElement.classList.add('avatar-female');
    } else if (patient && patient.gender === 'Male') {
        avatarElement.classList.add('avatar-male');
    }
    
    // Show warning modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deletePatientModal'));
    deleteModal.show();
}

function showDeleteConfirmation() {
    
    // Hide warning modal
    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deletePatientModal'));
    deleteModal.hide();
    
    // Reset confirmation modal
    resetDeleteConfirmation();
    
    // Show confirmation modal
    const confirmModal = new bootstrap.Modal(document.getElementById('deletePatientConfirmModal'));
    confirmModal.show();
    
    // Focus on text input
    setTimeout(() => {
        document.getElementById('deleteConfirmationText').focus();
    }, 300);
}

function backToDeleteWarning() {
    // Hide confirmation modal
    const confirmModal = bootstrap.Modal.getInstance(document.getElementById('deletePatientConfirmModal'));
    confirmModal.hide();
    
    // Show warning modal again
    setTimeout(() => {
        const deleteModal = new bootstrap.Modal(document.getElementById('deletePatientModal'));
        deleteModal.show();
    }, 300);
}

function resetDeleteConfirmation() {
    const confirmText = document.getElementById('deleteConfirmationText');
    const finalButton = document.getElementById('finalDeleteButton');
    const message = document.getElementById('deleteConfirmationMessage');
    
    confirmText.value = '';
    confirmText.classList.remove('is-valid', 'is-invalid');
    finalButton.disabled = true;
    message.classList.add('d-none');
}

function confirmPatientDeletion() {
    
    // Try to recover from localStorage if main variable is lost
    if (!window.currentPatientToDelete) {
        const savedData = localStorage.getItem('deletePatientData');
        if (savedData) {
            try {
                window.currentPatientToDelete = JSON.parse(savedData);
            } catch (e) {
                console.error('Failed to parse saved patient data:', e);
            }
        }
    }
    
    if (!window.currentPatientToDelete) {
        console.error('window.currentPatientToDelete is null or undefined');
        showDeleteMessage('Error: The patient was not selected for deletion', 'error');
        return;
    }
    
    const confirmText = document.getElementById('deleteConfirmationText');
    const enteredText = confirmText.value.trim().toUpperCase();
    
    if (enteredText !== 'DELETE' && enteredText !== 'DEL') {
        showDeleteMessage('The word (DELETE or DEL) must be typed in uppercase English letters', 'error');
        confirmText.focus();
        return;
    }
    
    // Show loading state
    setDeleteButtonLoading(true);
    
    // Send delete request
    fetch(`/api/patients/${window.currentPatientToDelete.id}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        setDeleteButtonLoading(false);
        
        if (data.ok) {
            // Success
            let successMsg = '✅ The patient was deleted successfully';
            if (data.data) {
                const attachments = data.data.attachments_deleted || 0;
                const files = data.data.files_deleted || 0;
                if (attachments > 0 || files > 0) {
                    successMsg += `\n📁 Deleted: ${attachments} attachments, ${files} files`;
                }
            }
            showDeleteMessage(successMsg, 'success');
            
            // Clean up data
            window.currentPatientToDelete = null;
            localStorage.removeItem('deletePatientData');
            
            // Close modal after delay and refresh page
            setTimeout(() => {
                const confirmModal = bootstrap.Modal.getInstance(document.getElementById('deletePatientConfirmModal'));
                confirmModal.hide();
                
                // Refresh page to update patient list and fix table dimensions
                window.location.reload();
            }, 1500);
            
        } else {
            // Error from server
            const errorMsg = data.error || data.message || 'Failed to delete the patient. Please try again.';
            showDeleteMessage(errorMsg, 'error');
        }
    })
    .catch(error => {
        setDeleteButtonLoading(false);
        console.error('Error deleting patient:', error);
        showDeleteMessage('An error occurred while deleting the patient. Please try again.', 'error');
    });
}

function showDeleteMessage(message, type) {
    const messageEl = document.getElementById('deleteConfirmationMessage');
    messageEl.className = `alert alert-${type === 'error' ? 'danger' : type}`;
    
    // Handle multi-line messages
    if (message.includes('\n')) {
        messageEl.innerHTML = message.split('\n').map(line => 
            line.trim() ? `<div>${line}</div>` : ''
        ).join('');
    } else {
        messageEl.textContent = message;
    }
    
    messageEl.classList.remove('d-none');
}

function setDeleteButtonLoading(loading) {
    const finalButton = document.getElementById('finalDeleteButton');
    const btnText = finalButton.querySelector('.btn-text');
    const spinner = finalButton.querySelector('.spinner-border');
    
    if (loading) {
        finalButton.disabled = true;
        btnText.textContent = 'Deleting...';
        spinner.classList.remove('d-none');
    } else {
        finalButton.disabled = false;
        btnText.textContent = 'Final Delete';
        spinner.classList.add('d-none');
    }
}

// Initialize delete confirmation functionality
document.addEventListener('DOMContentLoaded', function() {
    const deleteConfirmationText = document.getElementById('deleteConfirmationText');
    const finalDeleteButton = document.getElementById('finalDeleteButton');
    
    // Check if elements exist (only available on patients.php page)
    if (!deleteConfirmationText || !finalDeleteButton) {
        return; // Exit early if not on patients page
    }
    
    // Validate confirmation text input
    deleteConfirmationText.addEventListener('input', function() {
        const value = this.value.trim().toUpperCase();
        const isValid = value === 'DELETE' || value === 'DEL';
        
        if (value) {
            if (isValid) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                finalDeleteButton.disabled = false;
                showDeleteMessage('✓ The confirmation word is correct', 'success');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                finalDeleteButton.disabled = true;
                showDeleteMessage('The word (DELETE or DEL) must be typed in uppercase English letters', 'warning');
            }
        } else {
            this.classList.remove('is-valid', 'is-invalid');
            finalDeleteButton.disabled = true;
            document.getElementById('deleteConfirmationMessage').classList.add('d-none');
        }
    });
    
    // Handle Enter key in confirmation input
    deleteConfirmationText.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !finalDeleteButton.disabled) {
            e.preventDefault();
            confirmPatientDeletion();
        }
    });
    
    // Track modal states to prevent data loss during transitions
    let isTransitioning = false;
    
    // Reset confirmation when modal is hidden
    document.getElementById('deletePatientConfirmModal').addEventListener('hidden.bs.modal', function() {
        resetDeleteConfirmation();
        
        // Reset patient data only if not transitioning back to warning modal
        if (!isTransitioning) {
            window.currentPatientToDelete = null;
            localStorage.removeItem('deletePatientData');
        }
    });
    
    // Reset patient data when warning modal is hidden
    document.getElementById('deletePatientModal').addEventListener('hidden.bs.modal', function() {
        
        // Don't reset if we're transitioning to confirmation modal
        setTimeout(() => {
            if (!document.getElementById('deletePatientConfirmModal').classList.contains('show')) {
                window.currentPatientToDelete = null;
                localStorage.removeItem('deletePatientData');
            }
        }, 100); // Reduced timeout for faster response
    });
    
    // Override showDeleteConfirmation to prevent data loss
    const originalShowDeleteConfirmation = window.showDeleteConfirmation;
    window.showDeleteConfirmation = function() {
        isTransitioning = true;
        
        originalShowDeleteConfirmation();
        
        // Reset transition flag after modal is shown
        setTimeout(() => {
            isTransitioning = false;
        }, 500);
    };
    
    // Override backToDeleteWarning to prevent data loss
    const originalBackToDeleteWarning = window.backToDeleteWarning;
    window.backToDeleteWarning = function() {
        isTransitioning = true;
        
        originalBackToDeleteWarning();
        
        // Reset transition flag after modal is shown
        setTimeout(() => {
            isTransitioning = false;
        }, 500);
    };
});

// Initialize Bootstrap Tooltips
function initializeTooltips() {
    // Initialize tooltips for elements with data-bs-toggle="tooltip"
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
        boundary: 'viewport',
        fallbackPlacements: ['top', 'bottom', 'left', 'right'],
        sanitize: false, // Allow Arabic text
        html: false,
        delay: { show: 500, hide: 100 },
        trigger: 'hover focus'
    }));
    
    // Initialize tooltips for elements with title attribute (including modal trigger buttons)
    const titleElements = document.querySelectorAll('[title]:not([data-bs-toggle="tooltip"])');
    const titleTooltipList = [...titleElements].map(titleEl => new bootstrap.Tooltip(titleEl, {
        boundary: 'viewport',
        fallbackPlacements: ['top', 'bottom', 'left', 'right'],
        sanitize: false,
        html: false,
        delay: { show: 500, hide: 100 },
        trigger: 'hover focus'
    }));
    
    return [...tooltipList, ...titleTooltipList];
}

// Function to refresh tooltips for dynamically added content
function refreshTooltips() {
    // Dispose of existing tooltips
    const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"], [title]');
    existingTooltips.forEach(element => {
        const tooltip = bootstrap.Tooltip.getInstance(element);
        if (tooltip) {
            tooltip.dispose();
        }
    });
    
    // Reinitialize all tooltips
    initializeTooltips();
}

// Initialize tooltips when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
});

// Override displaySearchResults to include tooltip initialization
const originalDisplaySearchResults = displaySearchResults;
displaySearchResults = function(patients, searchTerm) {
    originalDisplaySearchResults(patients, searchTerm);
    
    // Initialize tooltips for newly added search result buttons
    setTimeout(() => {
        refreshTooltips();
    }, 100);
};

// Initialize phone tooltip click handlers
function initializePhoneTooltips() {
    // Use event delegation for dynamically added phone numbers
    document.addEventListener('click', function(e) {
        // Check if click is on a phone number link
        const phoneLink = e.target.closest('.phone-number-link');
        if (phoneLink) {
            e.preventDefault();
            e.stopPropagation();
            
            const container = phoneLink.closest('.phone-number-container');
            if (container) {
                // Toggle tooltip visibility
                const isActive = container.classList.contains('active');
                
                // Close all other tooltips
                document.querySelectorAll('.phone-number-container.active').forEach(activeContainer => {
                    if (activeContainer !== container) {
                        activeContainer.classList.remove('active');
                    }
                });
                
                // Toggle current tooltip
                if (isActive) {
                    container.classList.remove('active');
                } else {
                    container.classList.add('active');
                }
            }
        } else {
            // Close all tooltips when clicking outside
            document.querySelectorAll('.phone-number-container.active').forEach(container => {
                container.classList.remove('active');
            });
        }
    });
    
    // Prevent tooltip from closing when clicking inside it
    document.addEventListener('click', function(e) {
        if (e.target.closest('.phone-htooltip') || e.target.closest('.phone-action-btn')) {
            e.stopPropagation();
        }
    });
}

// Auto-refresh patients data every 60 seconds using AJAX (pause when modals are open or user is interacting)
let refreshInterval = setInterval(() => {
    const searchModal = document.getElementById('searchModal');
    const addPatientModal = document.getElementById('addPatientModal');
    const deleteModal = document.getElementById('deletePatientModal');
    const deleteConfirmModal = document.getElementById('deletePatientConfirmModal');
    const editPatientModal = document.getElementById('editPatientModal');
    const quickSearch = document.getElementById('quickSearch');
    
    // Don't refresh if user is actively using the page
    const isUserActive = document.activeElement === quickSearch || 
                        quickSearch.value.trim().length > 0 ||
                        paginationState.currentPage > 1 ||
                        paginationState.itemsPerPage !== 20 ||
                        document.querySelector('.modal.show') !== null;
    
    if (!searchModal.classList.contains('show') && 
        !addPatientModal.classList.contains('show') &&
        !deleteModal.classList.contains('show') &&
        !deleteConfirmModal.classList.contains('show') &&
        !editPatientModal?.classList.contains('show') &&
        !isUserActive) {
        refreshPatientsData();
    }
}, 60000);

// Function to refresh patients data via AJAX
function refreshPatientsData() {
    // Show subtle loading indicator
    const tableBody = document.getElementById('patientsTableBody');
    if (tableBody) {
        tableBody.parentElement.classList.add('table-loading');
    }
    
    // Build query parameters
    const params = new URLSearchParams();
    if (paginationState.sortBy) {
        params.append('sort_by', paginationState.sortBy);
    }
    if (paginationState.sortOrder) {
        params.append('sort_order', paginationState.sortOrder);
    }
    
    const url = '/api/patients' + (params.toString() ? '?' + params.toString() : '');
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.patients && data.doctors) {
            // Update pagination state
            paginationState.allPatients = data.patients;
            paginationState.doctors = data.doctors;
            
            // Reapply current filters
            applyDoctorFilter();
            
            // Reapply quick search if exists
            const quickSearch = document.getElementById('quickSearch');
            if (quickSearch && quickSearch.value.trim()) {
                filterPatientsLocally(quickSearch.value);
            } else {
                // Just re-render with current filters
                renderPatientsTable();
                updatePaginationInfo();
                renderPaginationNav();
            }
            
            // Update statistics
            updateStatistics(data.patients);
        }
    })
    .catch(error => {
        console.error('Error refreshing patients data:', error);
        // Silently fail - don't show error to user for background refresh
    })
    .finally(() => {
        // Remove loading indicator
        const tableBody = document.getElementById('patientsTableBody');
        if (tableBody) {
            tableBody.parentElement.classList.remove('table-loading');
        }
    });
}

// Function to update statistics cards
function updateStatistics(patients) {
    const totalPatients = patients.length;
    const totalVisits = patients.reduce((sum, p) => sum + (parseInt(p.total_appointments) || 0), 0);
    
    // Calculate recent visits (last 7 days)
    const recentVisits = patients.filter(p => {
        if (!p.last_visit) return false;
        const visitDate = new Date(p.last_visit);
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
        return visitDate >= sevenDaysAgo;
    }).length;
    
    // Calculate new this month (last 30 days)
    const newThisMonth = patients.filter(p => {
        if (!p.created_at) return false;
        const createdDate = new Date(p.created_at);
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        return createdDate >= thirtyDaysAgo;
    }).length;
    
    // Update statistics cards - use IDs for new stats cards
    const totalPatientsEl = document.getElementById('statsTotalPatients');
    const totalVisitsEl = document.getElementById('statsTotalVisits');
    const recentVisitsEl = document.getElementById('statsRecentVisits');
    const newThisMonthEl = document.getElementById('statsNewThisMonth');
    
    if (totalPatientsEl) totalPatientsEl.textContent = totalPatients;
    if (totalVisitsEl) totalVisitsEl.textContent = totalVisits;
    if (recentVisitsEl) recentVisitsEl.textContent = recentVisits;
    if (newThisMonthEl) newThisMonthEl.textContent = newThisMonth;
    
    // Fallback: Update old style cards if they exist
    const statsRow = document.querySelector('.row.mb-4');
    if (statsRow && !totalPatientsEl) {
        const statsCards = statsRow.querySelectorAll('.card-body h3');
        if (statsCards.length >= 4) {
            statsCards[0].textContent = totalPatients;
            statsCards[1].textContent = totalVisits;
            statsCards[2].textContent = recentVisits;
            statsCards[3].textContent = newThisMonth;
        }
    }
    
    // Update total count in header
    const totalCountElement = document.getElementById('totalPatientsCount');
    if (totalCountElement) {
        totalCountElement.textContent = totalPatients;
    }
}

// Edit Patient Function
function editPatient(patientId) {
    // Remove existing modal if present
    const existingModal = document.getElementById('editPatientModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Fetch patient data
    fetch(`/api/patients/${patientId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.ok || !data.patient) {
            showNotification('Error loading patient data', 'error');
            return;
        }
        
        const patient = data.patient;
        
        // Escape HTML for safety
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        const modalHtml = `
            <div class="modal fade" id="editPatientModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-person-gear me-2"></i>
                                Edit Patient Information
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="editPatientForm">
                            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                <!-- Basic Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3" style="color: var(--text); border-bottom-color: var(--border) !important;">Basic Information</h6>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="edit_first_name" name="first_name" 
                                               value="${escapeHtml(patient.first_name)}" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="edit_last_name" name="last_name" 
                                               value="${escapeHtml(patient.last_name)}" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <!-- Contact Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3" style="color: var(--text); border-bottom-color: var(--border) !important;">Contact Information</h6>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="edit_phone" name="phone" 
                                               value="${escapeHtml(patient.phone)}" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_alt_phone" class="form-label">Alternative Phone</label>
                                        <input type="tel" class="form-control" id="edit_alt_phone" name="alt_phone" 
                                               value="${escapeHtml(patient.alt_phone || '')}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <!-- Personal Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3" style="color: var(--text); border-bottom-color: var(--border) !important;">Personal Information</h6>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_dob" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="edit_dob" name="dob" 
                                               value="${patient.dob || ''}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_national_id" class="form-label">National ID</label>
                                        <input type="text" class="form-control" id="edit_national_id" name="national_id" 
                                               value="${escapeHtml(patient.national_id || '')}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <!-- Address -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3" style="color: var(--text); border-bottom-color: var(--border) !important;">Address</h6>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="edit_address" class="form-label">Full Address</label>
                                        <textarea class="form-control" id="edit_address" name="address" rows="3">${escapeHtml(patient.address || '')}</textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <!-- Emergency Contact -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3" style="color: var(--text); border-bottom-color: var(--border) !important;">Emergency Contact</h6>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_emergency_contact" class="form-label">Emergency Contact Name</label>
                                        <input type="text" class="form-control" id="edit_emergency_contact" name="emergency_contact" 
                                               value="${escapeHtml(patient.emergency_contact || '')}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_emergency_phone" class="form-label">Emergency Contact Phone</label>
                                        <input type="tel" class="form-control" id="edit_emergency_phone" name="emergency_phone" 
                                               value="${escapeHtml(patient.emergency_phone || '')}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-lg me-2"></i>Cancel
                                </button>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="resetEditPatientForm()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary" id="savePatientBtn">
                                    <span class="spinner-border spinner-border-sm d-none" id="savePatientSpinner"></span>
                                    <i class="bi bi-check-lg me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modalElement = document.getElementById('editPatientModal');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Store original form data for reset
        window.originalPatientData = {
            first_name: patient.first_name,
            last_name: patient.last_name,
            phone: patient.phone,
            alt_phone: patient.alt_phone || '',
            dob: patient.dob || '',
            national_id: patient.national_id || '',
            address: patient.address || '',
            emergency_contact: patient.emergency_contact || '',
            emergency_phone: patient.emergency_phone || ''
        };
        
        // Apply glass style and draggable
        setTimeout(function() {
            if (typeof applyGlassStyleToModal === 'function') {
                applyGlassStyleToModal('editPatientModal');
            }
            if (typeof initializeDraggableModals === 'function') {
                initializeDraggableModals();
            }
        }, 50);
        
        // Handle form submission
        document.getElementById('editPatientForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear previous validation errors
            clearEditPatientValidationErrors();
            
            // Basic validation
            if (!validateEditPatientForm()) {
                return;
            }
            
            // Show loading state
            const saveBtn = document.getElementById('savePatientBtn');
            const spinner = document.getElementById('savePatientSpinner');
            saveBtn.disabled = true;
            spinner.classList.remove('d-none');
            
            // Submit form
            const formData = new FormData(this);
            formData.append('_method', 'PUT');
            
            fetch(`/doctor/patients/${patientId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    showNotification('Patient updated successfully!', 'success');
                    modal.hide();
                    setTimeout(() => {
                        location.reload();
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
        
        // Clean up modal on hide
        modalElement.addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    })
    .catch(error => {
        console.error('Error loading patient data:', error);
        showNotification('Error loading patient data: ' + error.message, 'error');
    });
}

function validateEditPatientForm() {
    let isValid = true;
    
    // Required fields
    const requiredFields = ['edit_first_name', 'edit_last_name', 'edit_phone'];
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field || !field.value.trim()) {
            showEditPatientFieldError(fieldId, 'This field is required');
            isValid = false;
        }
    });
    
    // Phone validation
    const phone = document.getElementById('edit_phone')?.value.trim();
    if (phone && !isValidPhone(phone)) {
        showEditPatientFieldError('edit_phone', 'Please enter a valid phone number');
        isValid = false;
    }
    
    const altPhone = document.getElementById('edit_alt_phone')?.value.trim();
    if (altPhone && !isValidPhone(altPhone)) {
        showEditPatientFieldError('edit_alt_phone', 'Please enter a valid phone number');
        isValid = false;
    }
    
    const emergencyPhone = document.getElementById('edit_emergency_phone')?.value.trim();
    if (emergencyPhone && !isValidPhone(emergencyPhone)) {
        showEditPatientFieldError('edit_emergency_phone', 'Please enter a valid phone number');
        isValid = false;
    }
    
    return isValid;
}

function isValidPhone(phone) {
    // Egyptian phone number validation
    const phoneRegex = /^(\+20|0)?1[0-9]{9}$/;
    return phoneRegex.test(phone.replace(/\s+/g, ''));
}

function clearEditPatientValidationErrors() {
    const modal = document.getElementById('editPatientModal');
    if (!modal) return;
    
    modal.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    modal.querySelectorAll('.invalid-feedback').forEach(el => {
        el.textContent = '';
    });
}

function showEditPatientFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    const feedback = field.nextElementSibling;
    
    field.classList.add('is-invalid');
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = message;
    }
}

function resetEditPatientForm() {
    if (!window.originalPatientData) return;
    
    const data = window.originalPatientData;
    document.getElementById('edit_first_name').value = data.first_name;
    document.getElementById('edit_last_name').value = data.last_name;
    document.getElementById('edit_phone').value = data.phone;
    document.getElementById('edit_alt_phone').value = data.alt_phone;
    document.getElementById('edit_dob').value = data.dob;
    document.getElementById('edit_national_id').value = data.national_id;
    document.getElementById('edit_address').value = data.address;
    document.getElementById('edit_emergency_contact').value = data.emergency_contact;
    document.getElementById('edit_emergency_phone').value = data.emergency_phone;
    
    clearEditPatientValidationErrors();
}

// Confirmation Modal Helper
function showConfirmModal(title, message, onConfirm, confirmButtonText = 'Confirm') {
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const titleEl = document.getElementById('confirmModalTitle');
    const messageEl = document.getElementById('confirmModalMessage');
    const confirmBtn = document.getElementById('confirmModalButton');
    
    if (titleEl) titleEl.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>${escapeHtml(title)}`;
    if (messageEl) messageEl.textContent = message;
    if (confirmBtn) {
        confirmBtn.innerHTML = `<i class="bi bi-check-lg me-1"></i>${escapeHtml(confirmButtonText)}`;
        
        // Remove old event listeners by cloning
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        // Add new event listener
        newConfirmBtn.addEventListener('click', function() {
            modal.hide();
            if (onConfirm && typeof onConfirm === 'function') {
                onConfirm();
            }
        });
    }
    
    modal.show();
}

// Alert Modal Helper
function showAlertModal(title, message) {
    const modal = new bootstrap.Modal(document.getElementById('alertModal'));
    const titleEl = document.getElementById('alertModalTitle');
    const messageEl = document.getElementById('alertModalMessage');
    
    if (titleEl) titleEl.innerHTML = `<i class="bi bi-info-circle me-2"></i>${escapeHtml(title)}`;
    if (messageEl) messageEl.textContent = message;
    
    modal.show();
}

// Multi-selection helper functions
function toggleSelectionMode() {
    selectionMode = !selectionMode;
    if (!selectionMode) {
        // Clear selections when exiting selection mode
        selectedPatients = [];
        selectedFolders = [];
    }
    updateSelectionUI();
    updateSelectionModeButton();
}

function updateSelectionModeButton() {
    const label = document.getElementById('selectionModeLabel');
    if (label) {
        if (selectionMode) {
            label.innerHTML = '<i class="bi bi-x-lg me-1"></i>Cancel';
        } else {
            label.textContent = 'Select';
        }
    }
}

function renderFolderViewWithSelection() {
    // Re-render current folder view to show/hide checkboxes
    if (currentFolderId) {
        openFolder(currentFolderId);
    }
}

function togglePatientSelection(patientId) {
    const index = selectedPatients.indexOf(patientId);
    if (index > -1) {
        selectedPatients.splice(index, 1);
    } else {
        selectedPatients.push(patientId);
    }
    updateSelectionUI();
}

function toggleFolderSelection(folderId) {
    const index = selectedFolders.indexOf(folderId);
    if (index > -1) {
        selectedFolders.splice(index, 1);
    } else {
        selectedFolders.push(folderId);
    }
    updateSelectionUI();
}

function selectAllPatients() {
    const container = document.getElementById('folderPatientsContainer');
    if (!container) return;
    
    const checkboxes = container.querySelectorAll('input[type="checkbox"][data-patient-id]');
    selectedPatients = [];
    checkboxes.forEach(cb => {
        const patientId = parseInt(cb.getAttribute('data-patient-id'));
        if (patientId) {
            selectedPatients.push(patientId);
            cb.checked = true;
        }
    });
    updateSelectionUI();
}

function selectAllFolders() {
    const container = document.getElementById('patientsFoldersContainer');
    if (!container) return;
    
    const checkboxes = container.querySelectorAll('input[type="checkbox"][data-folder-id]');
    selectedFolders = [];
    checkboxes.forEach(cb => {
        const folderId = cb.getAttribute('data-folder-id');
        if (folderId) {
            selectedFolders.push(folderId);
            cb.checked = true;
        }
    });
    updateSelectionUI();
}

function deselectAll() {
    selectedPatients = [];
    selectedFolders = [];
    
    // Uncheck all checkboxes
    document.querySelectorAll('input[type="checkbox"][data-patient-id]').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[type="checkbox"][data-folder-id]').forEach(cb => cb.checked = false);
    
    updateSelectionUI();
}

function updateSelectionUI() {
    const totalSelected = selectedPatients.length + selectedFolders.length;
    
    // Show/hide bulk actions bar
    let bulkActionsBar = document.getElementById('bulkActionsBar');
    if (totalSelected > 0) {
        if (!bulkActionsBar) {
            renderBulkActionsBar();
        } else {
            updateBulkActionsBar();
        }
    } else {
        if (bulkActionsBar) {
            bulkActionsBar.remove();
        }
    }
    
    // Update selection count in bulk actions bar
    if (bulkActionsBar) {
        const countEl = bulkActionsBar.querySelector('.selection-count');
        if (countEl) {
            countEl.textContent = `${totalSelected} selected`;
        }
    }
}

function renderBulkActionsBar() {
    const container = document.getElementById('patientsFoldersContainer');
    if (!container) return;
    
    // Remove existing bar if any
    const existing = document.getElementById('bulkActionsBar');
    if (existing) existing.remove();
    
    const totalSelected = selectedPatients.length + selectedFolders.length;
    
    const bulkActionsHtml = `
        <div id="bulkActionsBar" class="bulk-actions-bar mb-3 p-3" style="background: var(--bg-alt); border: 1px solid var(--border); border-radius: 8px;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="selection-count fw-bold" style="color: var(--accent);">${totalSelected} selected</span>
                    ${selectedPatients.length > 0 ? `
                        <button class="btn btn-sm btn-outline-primary" onclick="selectAllPatients()">
                            <i class="bi bi-check-all me-1"></i>Select All Patients
                        </button>
                    ` : ''}
                    ${selectedFolders.length > 0 ? `
                        <button class="btn btn-sm btn-outline-primary" onclick="selectAllFolders()">
                            <i class="bi bi-check-all me-1"></i>Select All Folders
                        </button>
                    ` : ''}
                </div>
                <div class="d-flex align-items-center gap-2">
                    ${selectedPatients.length > 0 ? `
                        <button class="btn btn-sm btn-primary" onclick="bulkMovePatients()">
                            <i class="bi bi-folder me-1"></i>Move Selected (${selectedPatients.length})
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="bulkRemovePatientsFromFolder()">
                            <i class="bi bi-folder-minus me-1"></i>Remove (${selectedPatients.length})
                        </button>
                    ` : ''}
                    ${selectedFolders.length > 0 ? `
                        <button class="btn btn-sm btn-danger" onclick="bulkDeleteFolders()">
                            <i class="bi bi-trash me-1"></i>Delete Folders (${selectedFolders.length})
                        </button>
                    ` : ''}
                    <button class="btn btn-sm btn-secondary" onclick="deselectAll(); toggleSelectionMode();">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Insert at the beginning of container
    container.insertAdjacentHTML('afterbegin', bulkActionsHtml);
}

function updateBulkActionsBar() {
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    if (!bulkActionsBar) return;
    
    const totalSelected = selectedPatients.length + selectedFolders.length;
    const countEl = bulkActionsBar.querySelector('.selection-count');
    if (countEl) {
        countEl.textContent = `${totalSelected} selected`;
    }
    
    // Update button counts
    const moveBtn = bulkActionsBar.querySelector('button[onclick="bulkMovePatients()"]');
    if (moveBtn && selectedPatients.length > 0) {
        moveBtn.innerHTML = `<i class="bi bi-folder me-1"></i>Move Selected (${selectedPatients.length})`;
    }
    
    const removeBtn = bulkActionsBar.querySelector('button[onclick="bulkRemovePatientsFromFolder()"]');
    if (removeBtn && selectedPatients.length > 0) {
        removeBtn.innerHTML = `<i class="bi bi-folder-minus me-1"></i>Remove (${selectedPatients.length})`;
    }
    
    const deleteBtn = bulkActionsBar.querySelector('button[onclick="bulkDeleteFolders()"]');
    if (deleteBtn && selectedFolders.length > 0) {
        deleteBtn.innerHTML = `<i class="bi bi-trash me-1"></i>Delete Folders (${selectedFolders.length})`;
    }
}

function bulkMovePatients() {
    if (selectedPatients.length === 0) return;
    
    // Store selected patients for bulk move
    window.bulkMovePatientIds = [...selectedPatients];
    
    // Use first patient to show modal (will move all selected)
    showMovePatientModal(selectedPatients[0], true);
}

function bulkRemovePatientsFromFolder() {
    if (selectedPatients.length === 0 || !currentFolderId) return;
    
    showConfirmModal(
        'Remove Patients',
        `Are you sure you want to remove ${selectedPatients.length} patient(s) from this folder?`,
        function() {
            performBulkRemovePatients();
        },
        'Remove'
    );
}

function performBulkRemovePatients() {
    if (selectedPatients.length === 0 || !currentFolderId) return;
    
    // Remove patients one by one (or create bulk endpoint)
    let completed = 0;
    let failed = 0;
    
    selectedPatients.forEach(patientId => {
        fetch(`/api/patient-folders/${currentFolderId}/patients/${patientId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                completed++;
            } else {
                failed++;
            }
            
            if (completed + failed === selectedPatients.length) {
                selectedPatients = [];
                updateSelectionUI();
                openFolder(currentFolderId);
                // Also refresh patients data for cards/table views
                refreshPatientsData();
                showNotification(`${completed} patient(s) removed successfully${failed > 0 ? `, ${failed} failed` : ''}`, completed > 0 ? 'success' : 'error');
            }
        })
        .catch(error => {
            failed++;
            if (completed + failed === selectedPatients.length) {
                selectedPatients = [];
                updateSelectionUI();
                openFolder(currentFolderId);
                // Also refresh patients data for cards/table views
                refreshPatientsData();
                showNotification(`Error removing patients. ${completed} succeeded, ${failed} failed.`, 'error');
            }
        });
    });
}

function bulkDeleteFolders() {
    if (selectedFolders.length === 0) return;
    
    showConfirmModal(
        'Delete Folders',
        `Are you sure you want to delete ${selectedFolders.length} folder(s)? Patients will not be deleted, only removed from the folders.`,
        function() {
            performBulkDeleteFolders();
        },
        'Delete'
    );
}

function performBulkDeleteFolders() {
    if (selectedFolders.length === 0) return;
    
    const folderIdsToDelete = selectedFolders.map(id => {
        // Convert system folder IDs to numeric if needed
        if (id.toString().startsWith('system_')) {
            return id; // Keep as is for now, but API won't find it
        }
        return parseInt(id);
    }).filter(id => !id.toString().startsWith('system_')); // Only custom folders can be deleted
    
    fetch('/api/patient-folders/bulk', {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            folder_ids: folderIdsToDelete
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            selectedFolders = [];
            updateSelectionUI();

            // Reload folders via API - refresh entire view
            if (currentFolderId) {
                // Inside a folder - refresh the full folder view (sub-folders + patients)
                openFolder(currentFolderId);
            } else {
                // At root folders view - reload all folders
                loadFolders();
            }

            showNotification(data.message || `${data.deleted_count || 0} folder(s) deleted successfully`, 'success');
        } else {
            showNotification(data.error || 'Failed to delete folders', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting folders:', error);
        showNotification('An error occurred while deleting folders', 'error');
    });
}

// Check if showNotification exists from main layout, otherwise define it
if (typeof window.showNotification !== 'function') {
    window.showNotification = function(message, type = 'info') {
        // Fallback notification
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
    };
}

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

// Sort patients function
function sortPatients(sortBy, sortOrder) {
    // Update sort state
    paginationState.sortBy = sortBy;
    paginationState.sortOrder = sortOrder;
    
    // Reset to first page
    paginationState.currentPage = 1;
    
    // Update sort button states
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    const activeBtn = document.querySelector(`[data-sort="${sortBy}"][data-order="${sortOrder}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
    
    // Show loading indicator
    const tableBody = document.getElementById('patientsTableBody');
    if (tableBody) {
        tableBody.parentElement.classList.add('table-loading');
    }
    
    // Build query parameters
    const params = new URLSearchParams();
    params.append('sort_by', sortBy);
    params.append('sort_order', sortOrder);
    
    // Fetch sorted data
    fetch('/api/patients?' + params.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.patients && data.doctors) {
            // Update pagination state
            paginationState.allPatients = data.patients;
            paginationState.doctors = data.doctors;
            
            // Reapply current filters
            applyDoctorFilter();
            
            // Reapply quick search if exists
            const quickSearch = document.getElementById('quickSearch');
            if (quickSearch && quickSearch.value.trim()) {
                filterPatientsLocally(quickSearch.value);
            } else {
                // Just re-render with current filters
                renderPatientsTable();
                updatePaginationInfo();
                renderPaginationNav();
            }
            
            // Update statistics
            updateStatistics(data.patients);
            
            // Update clear filters button visibility
            updateClearFiltersVisibility();
        }
    })
    .catch(error => {
        console.error('Error sorting patients:', error);
        showNotification('Error sorting patients. Please try again.', 'error');
    })
    .finally(() => {
        // Remove loading indicator
        const tableBody = document.getElementById('patientsTableBody');
        if (tableBody) {
            tableBody.parentElement.classList.remove('table-loading');
        }
    });
}

// OLD FILTER CODE REMOVED - Using UnifiedFilterManager instead
// OLD FILTER POPOVER FUNCTIONS REMOVED - Using UnifiedFilterManager instead

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initCustomSelects();
    
    // Initialize sort buttons
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const sortBy = this.dataset.sort;
            const sortOrder = this.dataset.order;
            
            if (sortBy && sortOrder) {
                sortPatients(sortBy, sortOrder);
            }
        });
    });
    
    // OLD FILTER POPOVER INITIALIZATION REMOVED - Using UnifiedFilterManager instead
    
    // Initialize clear filters buttons
    const clearAllFiltersBtn = document.querySelector('.clear-all-filters-btn');
    if (clearAllFiltersBtn) {
        clearAllFiltersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearAllFilters();
        });
    }
    
    const clearSortingBtn = document.getElementById('clearSortingBtn');
    if (clearSortingBtn) {
        clearSortingBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearSorting();
        });
    }
    
    // Initial visibility check
    updateClearFiltersVisibility();
});

// Also initialize when modals are shown
document.addEventListener('shown.bs.modal', function(e) {
    const modal = e.target;
    setTimeout(() => {
        initCustomSelects();
    }, 100);
});

// ============================================
// Cards View Functions
// ============================================

// Render patients as cards
function renderPatientsCards() {
    const container = document.getElementById('patientsCardsContainer');
    const { currentPage, itemsPerPage, filteredPatients } = paginationState;
    
    // Calculate pagination
    let startIndex, endIndex, patientsToShow;
    
    // Use different items per page for cards if available
    const cardsItemsPerPage = document.getElementById('paginationLimitCards') 
        ? parseInt(document.getElementById('paginationLimitCards').value) || 24 
        : 24;
    
    if (cardsItemsPerPage === 'all' || cardsItemsPerPage === 'All') {
        startIndex = 0;
        endIndex = filteredPatients.length;
        patientsToShow = filteredPatients;
    } else {
        startIndex = (currentPage - 1) * cardsItemsPerPage;
        endIndex = Math.min(startIndex + cardsItemsPerPage, filteredPatients.length);
        patientsToShow = filteredPatients.slice(startIndex, endIndex);
    }
    
    // Clear container
    container.innerHTML = '';
    
    if (patientsToShow.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No patients to display</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    patientsToShow.forEach(patient => {
        const age = patient.dob ? calculateAge(patient.dob) : null;
        const ageText = age !== null ? `${age} years` : 'Not specified';
        const lastAppointment = patient.last_appointment_datetime 
            ? formatDate(patient.last_appointment_datetime) 
            : (patient.last_visit ? formatDate(patient.last_visit) : 'Not visited yet');
        
        const firstName = patient.first_name || '';
        const lastName = patient.last_name || '';
        const fullName = `${firstName} ${lastName}`.trim();
        
        // Get patient image URL (using dedicated endpoint for cards/folders views)
        const imageUrl = patient.latest_attachment_id 
            ? `/api/patients/images/${patient.latest_attachment_id}`
            : null;
        
        // Gender icon
        const genderIcon = patient.gender === 'Female' 
            ? '<i class="bi bi-gender-female"></i>' 
            : (patient.gender === 'Male' 
                ? '<i class="bi bi-gender-male"></i>' 
                : '<i class="bi bi-gender-ambiguous"></i>');
        
        const genderBadgeClass = patient.gender === 'Female' 
            ? 'bg-pink' 
            : (patient.gender === 'Male' ? 'bg-primary' : 'bg-secondary');
        
        // Get color marker (will be fetched and cached)
        const colorMarker = patient.color_marker || null;
        
        // Get card size from localStorage for cards view
        const cardSize = localStorage.getItem('cardsViewCardSize') || 'small';
        const sizeClass = {
            small: 'col-md-6 col-lg-4 col-xl-3',
            medium: 'col-md-4 col-lg-3',
            large: 'col-md-3 col-lg-2'
        }[cardSize] || 'col-md-6 col-lg-4 col-xl-3';
        
        html += `
            <div class="${sizeClass} mb-3">
                <div class="card patient-card clickable h-100 ${colorMarker ? 'patient-card-has-marker' : ''}" 
                     data-patient-id="${patient.id}"
                     data-has-color-marker="${colorMarker ? 'true' : 'false'}"
                     style="${colorMarker ? `--marker-color: ${colorMarker}; --marker-color-rgb: ${hexToRgb(colorMarker)};` : ''} border: ${colorMarker ? `2px solid ${colorMarker}` : '1px solid var(--border)'}; cursor: pointer; position: relative;" 
                     onclick="viewPatient(${patient.id})">
                    ${colorMarker ? `
                        <div class="patient-color-marker" 
                             style="position: absolute; top: 8px; right: 8px; width: 12px; height: 12px; border-radius: 50%; background: ${colorMarker}; border: 2px solid white; z-index: 5; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"
                             onclick="event.stopPropagation(); showColorMarkerModal(${patient.id}, '${colorMarker}')"
                             title="Click to change color marker"></div>
                    ` : `
                        <div class="patient-color-marker-add" 
                             onclick="event.stopPropagation(); showColorMarkerModal(${patient.id}, null)"
                             title="Click to add color marker">
                            <i class="bi bi-plus-lg"></i>
                        </div>
                    `}
                    <!-- Patient Image -->
                    <div class="position-relative patient-card-image-container" style="height: 200px; overflow: hidden; background: var(--bg-alt);">
                        ${imageUrl ? `
                            <img data-src="${imageUrl}" 
                                 alt="${escapeHtml(fullName)}" 
                                 class="w-100 h-100 patient-card-image lazy-load" 
                                 style="object-fit: cover; opacity: 0; transition: opacity 0.3s ease;"
                                 data-patient-id="${patient.id}"
                                 data-image-url="${imageUrl}"
                                 onerror="(function(img, url, pid) { img.style.display='none'; img.style.opacity='0'; const placeholder = img.nextElementSibling; if (placeholder) { placeholder.style.display='flex'; } })(this, '${imageUrl}', ${patient.id});"
                                 onload="(function(img) { img.style.opacity='1'; img.style.display='block'; const placeholder = img.nextElementSibling; if (placeholder) { placeholder.style.display='none'; } })(this);">
                        ` : ''}
                        <div class="d-flex align-items-center justify-content-center h-100 w-100 patient-card-placeholder" 
                             style="background: linear-gradient(135deg, var(--accent) 0%, var(--bg-alt) 100%); ${imageUrl ? 'display: none;' : 'display: flex;'}">
                            <i class="bi bi-person-circle text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <!-- Badges overlay -->
                        <div class="position-absolute top-0 start-0 p-2 d-flex gap-2 flex-wrap">
                            ${age !== null ? `
                                <span class="badge bg-info">${age} years</span>
                            ` : ''}
                            <span class="badge ${genderBadgeClass}">${genderIcon}</span>
                        </div>
                        ${patient.last_clinic_id ? `
                            <!-- Last visit clinic badge (top-right) -->
                            <div class="position-absolute p-2" style="top: ${colorMarker ? '30px' : '0'}; right: 0;">
                                ${renderClinicBadge(patient, { compact: true })}
                            </div>
                        ` : ''}
                        <!-- Treatment Doctor Badge -->
                        ${patient.created_by_doctor_name ? `
                            <div class="position-absolute bottom-0 start-0 p-2">
                                <span class="badge bg-primary" style="font-size: 0.7rem; backdrop-filter: blur(4px); background: rgba(14, 165, 233, 0.9) !important; border: 1px solid rgba(255, 255, 255, 0.3);">
                                    TD: ${escapeHtml(patient.created_by_doctor_name)}
                                </span>
                            </div>
                        ` : ''}
                    </div>
                    
                    <!-- Card Body -->
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title mb-2">
                            <span style="color: var(--accent); font-weight: 600;">
                                ${escapeHtml(fullName)}
                            </span>
                        </h6>
                        
                        <div class="mb-2">
                            ${patient.phone ? `
                                <small class="text-muted d-block">
                                    <i class="bi bi-telephone me-1"></i>
                                    <div class="phone-number-container" style="position: relative; display: inline-block;" onclick="event.stopPropagation();">
                                        <a href="tel:${escapeHtml(patient.phone)}" 
                                           class="phone-number-link" 
                                           style="text-decoration: none; color: var(--accent); font-weight: 500; cursor: pointer; transition: all 0.2s ease;">
                                            ${escapeHtml(patient.phone)}
                                        </a>
                                        <span class="phone-htooltip">
                                            <div class="phone-actions">
                                                <a href="tel:${escapeHtml(patient.phone)}" class="phone-action-btn" title="Call" onclick="event.stopPropagation();">
                                                    <i class="bi bi-telephone-fill"></i>
                                                    <span>Call</span>
                                                </a>
                                                <a href="https://wa.me/+2${escapeHtml(patient.phone).replace(/[^0-9]/g, '')}" target="_blank" class="phone-action-btn whatsapp-btn" title="WhatsApp" onclick="event.stopPropagation();">
                                                    <i class="bi bi-whatsapp"></i>
                                                    <span>WhatsApp</span>
                                                </a>
                                            </div>
                                        </span>
                                    </div>
                                </small>
                            ` : ''}
                        </div>
                        
                        <div class="mt-auto pt-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    ${patient.total_appointments || 0} visits
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    ${lastAppointment}
                                </small>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-capsule me-1"></i>
                                    ${patient.prescriptions_count || 0} prescriptions
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-eyeglasses me-1"></i>
                                    ${patient.glasses_count || 0} glasses
                                </small>
                            </div>
                            
                            <!-- Patient Tags -->
                            <div class="mb-2" id="patientTags_${patient.id}"></div>
                            
                            <!-- Quick Actions -->
                            <div class="d-flex gap-1 justify-content-end mt-2 pt-2 border-top" onclick="event.stopPropagation();">
                                <a href="/doctor/patients/${patient.id}" 
                                   class="card-action-btn card-action-view" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   data-bs-title="View patient"
                                   onclick="event.stopPropagation();">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="card-action-btn card-action-book" 
                                        onclick="event.stopPropagation(); bookAppointment(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Book appointment">
                                    <i class="bi bi-calendar-plus"></i>
                                </button>
                                <button class="card-action-btn card-action-edit"
                                        onclick="event.stopPropagation(); editPatient(${patient.id})"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-title="Edit patient">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="card-action-btn card-action-delete"
                                        onclick="event.stopPropagation(); deletePatient(${patient.id}, '${escapeHtml(fullName).replace(/'/g, "\\'")}')"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-title="Delete patient">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Initialize lazy loading for images
    initLazyLoading();

    // Refresh tooltips
    setTimeout(() => {
        refreshTooltips();
    }, 100);
    
    // Fetch color markers for all patients (only for current page)
    fetchColorMarkersForPatients(patientsToShow);
    
    // Fetch tags for all patients (only for current page)
    fetchTagsForPatients(patientsToShow);
}

// Update pagination info for cards
function updatePaginationInfoCards() {
    const { currentPage, filteredPatients } = paginationState;
    const cardsItemsPerPage = document.getElementById('paginationLimitCards') 
        ? parseInt(document.getElementById('paginationLimitCards').value) || 24 
        : 24;
    
    document.getElementById('totalPatientsCountCards').textContent = filteredPatients.length;
    document.getElementById('totalPatientsCards').textContent = filteredPatients.length;
    
    if (cardsItemsPerPage === 'all' || cardsItemsPerPage === 'All') {
        document.getElementById('showingFromCards').textContent = filteredPatients.length > 0 ? '1' : '0';
        document.getElementById('showingToCards').textContent = filteredPatients.length;
        document.getElementById('paginationNavCards').style.display = 'none';
    } else {
        const startIndex = (currentPage - 1) * cardsItemsPerPage + 1;
        const endIndex = Math.min(currentPage * cardsItemsPerPage, filteredPatients.length);
        
        document.getElementById('showingFromCards').textContent = filteredPatients.length > 0 ? startIndex : '0';
        document.getElementById('showingToCards').textContent = endIndex;
        document.getElementById('paginationNavCards').style.display = 'flex';
    }
}

// Render pagination nav for cards
function renderPaginationNavCards() {
    const { currentPage, filteredPatients } = paginationState;
    const cardsItemsPerPage = document.getElementById('paginationLimitCards') 
        ? parseInt(document.getElementById('paginationLimitCards').value) || 24 
        : 24;
    
    if (cardsItemsPerPage === 'all' || cardsItemsPerPage === 'All') {
        return;
    }
    
    const totalPages = Math.ceil(filteredPatients.length / cardsItemsPerPage);
    const nav = document.getElementById('paginationNavCards');
    
    if (totalPages <= 1) {
        nav.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">Previous</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Next button
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">Next</a>
        </li>
    `;
    
    nav.innerHTML = html;
}

// Change page (works for both table and cards)
function changePage(page) {
    paginationState.currentPage = page;
    
    if (currentViewMode === 'table') {
        renderPatientsTable();
        updatePaginationInfo();
        renderPaginationNav();
    } else if (currentViewMode === 'cards') {
        initCardSizeCards(); // Initialize card size for cards view
        renderPatientsCards();
        updatePaginationInfoCards();
        renderPaginationNavCards();
    }
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Change page for folders view
function changeFolderPage(page) {
    folderPaginationState.currentPage = page;
    
    if (currentFolderPatients && currentFolderPatients.length > 0) {
        renderFolderPatients(folderPaginationState.filteredPatients);
    }
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Change items per page for folders view
function changeFolderItemsPerPage(newLimit) {
    if (newLimit === 'all') {
        folderPaginationState.itemsPerPage = 'all';
    } else {
        folderPaginationState.itemsPerPage = parseInt(newLimit);
    }
    folderPaginationState.currentPage = 1;
    
    // Save to localStorage
    localStorage.setItem('folderItemsPerPage', folderPaginationState.itemsPerPage.toString());
    
    if (currentFolderPatients && currentFolderPatients.length > 0) {
        renderFolderPatients(folderPaginationState.filteredPatients);
    }
}

// Render pagination navigation for folders view
function renderFolderPaginationNav() {
    const { currentPage, itemsPerPage, filteredPatients } = folderPaginationState;
    const totalPages = (itemsPerPage === 'all' || itemsPerPage >= filteredPatients.length) ? 1 : Math.ceil(filteredPatients.length / itemsPerPage);
    
    const nav = document.getElementById('folderPaginationNav');
    
    if (!nav) {

        return;
    }
    
    
    if (totalPages <= 1) {
        nav.innerHTML = '';
        return;
    }
    
    let html = `
        <nav aria-label="Folder patients pagination">
            <ul class="pagination justify-content-center mb-0">
    `;
    
    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changeFolderPage(${currentPage - 1}); return false;">Previous</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changeFolderPage(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Next button
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changeFolderPage(${currentPage + 1}); return false;">Next</a>
        </li>
    `;
    
    html += `
            </ul>
        </nav>
    `;
    
    nav.innerHTML = html;
}

// Update pagination info for folders view
function updateFolderPaginationInfo() {
    const { currentPage, itemsPerPage, filteredPatients } = folderPaginationState;
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, filteredPatients.length);
    const info = document.getElementById('folderPaginationInfo');
    
    if (!info) {
        console.warn('[Pagination] folderPaginationInfo element not found in DOM');
        return;
    }
    
    if (filteredPatients.length > 0) {
        const actualEndIndex = (itemsPerPage === 'all' || itemsPerPage >= filteredPatients.length) ? filteredPatients.length : endIndex;
        const actualStartIndex = (itemsPerPage === 'all' || itemsPerPage >= filteredPatients.length) ? 1 : startIndex + 1;
        
        info.innerHTML = `
            <div class="text-center text-muted">
                <small>
                    Showing <span id="folderShowingFrom">${actualStartIndex}</span> to 
                    <span id="folderShowingTo">${actualEndIndex}</span> of 
                    <span id="folderTotalPatients">${filteredPatients.length}</span> patients
                </small>
            </div>
        `;
        
        // Update per page selector at top
        const perPageSelector = document.getElementById('folderItemsPerPageSelector');
        if (perPageSelector) {
            perPageSelector.value = (itemsPerPage === 'all' || itemsPerPage >= filteredPatients.length) ? 'all' : itemsPerPage.toString();
        }
    } else {
        info.innerHTML = '';
    }
}

// ============================================
// Folders View Functions
// ============================================

// Auto-refresh interval for folders
let foldersRefreshInterval = null;

// Start auto-refresh for folders
function startFoldersAutoRefresh() {
    if (foldersRefreshInterval) clearInterval(foldersRefreshInterval);
    foldersRefreshInterval = setInterval(() => {
        if (currentViewMode === 'folders' && !currentFolderId) {
            loadFolders();
        }
    }, 30000); // كل 30 ثانية
}

// Stop auto-refresh for folders
function stopFoldersAutoRefresh() {
    if (foldersRefreshInterval) {
        clearInterval(foldersRefreshInterval);
        foldersRefreshInterval = null;
    }
}

// Update folder patient count locally
function updateFolderPatientCount(folderId, increment = 1) {
    const folder = foldersData.find(f => f.id === folderId);
    if (folder) {
        folder.patient_count = Math.max(0, (folder.patient_count || 0) + increment);
        renderFoldersView();
    }
}

// Separate data storage for system and custom folders
let systemFoldersData = [];
let customFoldersData = [];

// Load folders from API
// skipRender: if true, don't call renderFoldersView (used when restoring folder state)
function loadFolders(skipRender = false) {
    // Invalidate folder cache when reloading folders (usually after modification)
    if (!skipRender) {
        folderCache.invalidate();
    }

    return fetch('/api/patient-folders', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Separate system and custom folders
            systemFoldersData = data.system_folders || [];
            customFoldersData = data.custom_folders || [];

            // Apply system folder preferences from localStorage
            const systemFolderPrefs = JSON.parse(localStorage.getItem('systemFolderPreferences') || '{}');
            systemFoldersData.forEach(folder => {
                if (systemFolderPrefs[folder.id]) {
                    if (systemFolderPrefs[folder.id].icon) {
                        folder.icon = systemFolderPrefs[folder.id].icon;
                    }
                    if (systemFolderPrefs[folder.id].gradient_color) {
                        folder.gradient_color = systemFolderPrefs[folder.id].gradient_color;
                    }
                }
            });

            // Keep foldersData for backward compatibility (merged)
            foldersData = [...systemFoldersData, ...customFoldersData];

            // Initialize or update FolderTreeview
            if (!folderTreeview) {
                folderTreeview = new FolderTreeview('folderTreeview', {
                    onFolderClick: (folderId) => {
                        openFolder(folderId);
                    }
                });
            }
            
            // Update treeview with new data
            folderTreeview.updateTree({
                systemFolders: systemFoldersData,
                customFolders: customFoldersData
            });

            // Only render folders view if not skipping (e.g., when restoring folder state)
            if (!skipRender) {
                renderFoldersView();
            }
            
            // OLD FILTER MANAGER INIT REMOVED - Using UnifiedFilterManager instead
        }
        return data;
    })
    .catch(error => {
        console.error('Error loading folders:', error);
        throw error;
    });
}

// Render folders view with pagination
let foldersPage = 1;
const foldersPerPage = 12; // Show 12 folders per page

function renderFoldersView(page = 1, clearState = true) {
    foldersPage = page;

    // Only clear folder state when explicitly requested (user clicked back to root)
    if (clearState) {
        // Clear folder path stack and state when returning to main folders view
        folderPathStack = [];
        currentFolderId = null;
        currentFolderName = null;
        currentFolderType = null;
        // Clear all storage
        clearFolderNavigationState();
    }

    // Clear selection mode
    selectionMode = false;
    selectedPatients = [];
    selectedFolders = [];
    updateSelectionUI();

    // Clear search
    const searchInput = document.getElementById('folderSearchInput');
    if (searchInput) {
        searchInput.value = '';
    }
    const clearBtn = document.getElementById('clearFolderSearch');
    if (clearBtn) {
        clearBtn.style.display = 'none';
    }
    
    // View toggle moved to unified filter bar - no need to show/hide header toggle
    
    // Show Create Folder button
    const createFolderBtn = document.querySelector('button[onclick="showCreateFolderModal()"]');
    if (createFolderBtn) {
        createFolderBtn.style.display = 'inline-block';
    }
    
    // Hide folderContentArea and show patientsFoldersContainer
    const folderContentArea = document.getElementById('folderContentArea');
    const patientsFoldersContainer = document.getElementById('patientsFoldersContainer');
    
    if (folderContentArea) {
        folderContentArea.style.display = 'none';
    }
    if (patientsFoldersContainer) {
        patientsFoldersContainer.style.display = 'block';
    }
    
    // Update treeview active folder
    if (folderTreeview) {
        folderTreeview.highlightActive(null);
    }
    
    const container = document.getElementById('patientsFoldersContainer');
    
    if (!container) {
        console.warn('patientsFoldersContainer not found');
        return;
    }
    
    // Check if both are empty
    if (systemFoldersData.length === 0 && customFoldersData.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-folder-x text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No folders available</p>
                <button class="btn btn-success mt-3" onclick="showCreateFolderModal()">
                    <i class="bi bi-folder-plus me-1"></i>
                    Create Your First Folder
                </button>
            </div>
        `;
        return;
    }
    
    // Hide Create Folder button when viewing System Folders
    if (createFolderBtn && systemFoldersData.length > 0) {
        createFolderBtn.style.display = 'none';
    } else if (createFolderBtn) {
        createFolderBtn.style.display = 'inline-block';
    }
    
    let html = '';
    
    // System Folders Section
    if (systemFoldersData.length > 0) {
        html += `
            <div class="system-folders-section mb-4">
                <h5 class="mb-3" style="color: var(--text); font-weight: 600;">
                    <i class="bi bi-folder-fill me-2" style="color: var(--accent);"></i>
                    System Folders
                </h5>
                <div class="row g-3">
        `;
        
        // Paginate system folders
        const systemStart = (foldersPage - 1) * foldersPerPage;
        const systemEnd = systemStart + foldersPerPage;
        const systemPaginated = systemFoldersData.slice(systemStart, systemEnd);
        
        systemPaginated.forEach(folder => {
            html += renderFolderCard(folder, true);
        });
        
        html += `
                </div>
            </div>
        `;
        
        // Add pagination for system folders if needed
        const totalSystemPages = Math.ceil(systemFoldersData.length / foldersPerPage);
        if (totalSystemPages > 1) {
            html += renderFoldersPagination(totalSystemPages, foldersPage, 'system');
        }
    }
    
    // Custom Folders Section
    if (customFoldersData.length > 0) {
        html += `
            <div class="custom-folders-section">
                <h5 class="mb-3" style="color: var(--text); font-weight: 600;">
                    <i class="bi bi-folder me-2" style="color: var(--accent);"></i>
                    Your Custom Folders
                </h5>
                <div class="row g-3">
        `;
        
        // Paginate custom folders
        const customStart = (foldersPage - 1) * foldersPerPage;
        const customEnd = customStart + foldersPerPage;
        const customPaginated = customFoldersData.slice(customStart, customEnd);
        
        customPaginated.forEach(folder => {
            html += renderFolderCard(folder, false);
        });
        
        html += `
                </div>
            </div>
        `;
        
        // Add pagination for custom folders if needed
        const totalCustomPages = Math.ceil(customFoldersData.length / foldersPerPage);
        if (totalCustomPages > 1) {
            html += renderFoldersPagination(totalCustomPages, foldersPage, 'custom');
        }
    }
    
    container.innerHTML = html;
}

// Render pagination for folders
function renderFoldersPagination(totalPages, currentPage, folderType) {
    if (totalPages <= 1) return '';
    
    let html = `
        <div class="folders-pagination mt-3 d-flex justify-content-center">
            <nav aria-label="Folders pagination">
                <ul class="pagination mb-0">
    `;
    
    // Previous button
    if (currentPage > 1) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="event.preventDefault(); renderFoldersView(${currentPage - 1}); return false;">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;
    }
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="event.preventDefault(); renderFoldersView(${i}); return false;">${i}</a>
                </li>
            `;
        }
    }
    
    // Next button
    if (currentPage < totalPages) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="event.preventDefault(); renderFoldersView(${currentPage + 1}); return false;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;
    }
    
    html += `
                </ul>
            </nav>
        </div>
    `;
    
    return html;
}

// Render a single folder card (helper function)
function renderFolderCard(folder, isSystem) {
    const folderId = folder.id;
    
    // Get folder icon (default or custom)
    // Check localStorage for system folders first
    let folderIcon = folder.icon;
    if (isSystem) {
        const systemFolderPrefs = JSON.parse(localStorage.getItem('systemFolderPreferences') || '{}');
        if (systemFolderPrefs[folderId] && systemFolderPrefs[folderId].icon) {
            folderIcon = systemFolderPrefs[folderId].icon;
        } else if (!folderIcon) {
            folderIcon = 'bi-folder-fill';
        }
    } else {
        if (!folderIcon) {
            folderIcon = 'bi-folder';
        }
    }
    
    // Get gradient color (default or custom)
    // Check localStorage for system folders first
    let gradientColor = folder.gradient_color;
    if (isSystem) {
        const systemFolderPrefs = JSON.parse(localStorage.getItem('systemFolderPreferences') || '{}');
        if (systemFolderPrefs[folderId] && systemFolderPrefs[folderId].gradient_color) {
            gradientColor = systemFolderPrefs[folderId].gradient_color;
        } else if (!gradientColor) {
            gradientColor = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        }
    } else {
        // Check localStorage for custom folders too (in case they were customized)
        const customFolderPrefs = JSON.parse(localStorage.getItem('customFolderPreferences') || '{}');
        if (customFolderPrefs[folderId] && customFolderPrefs[folderId].gradient_color) {
            gradientColor = customFolderPrefs[folderId].gradient_color;
        } else if (!gradientColor) {
            // Updated to Blue Gradient as per request
            gradientColor = 'linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%)';
        }
    }
    
    // Get doctor image for system folders
    let doctorImageHtml = '';
    if (isSystem && folder.profile_image) {
        doctorImageHtml = `
            <img src="${escapeHtml(folder.profile_image)}" 
                 alt="${escapeHtml(folder.name)}" 
                 class="folder-doctor-avatar"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        `;
    }
    
    // Get doctor initial
    const doctorInitial = folder.name ? folder.name.charAt(0).toUpperCase() : 'F';
    
    // Escape gradient for use in style attribute - use single quotes to avoid issues
    const safeGradient = gradientColor.replace(/'/g, "\\'");
    
    // Use smaller columns for custom folders
    const columnClass = isSystem ? 'col-md-4 col-lg-3' : 'col-md-3 col-lg-2 col-xl-2';
    
    return `
        <div class="${columnClass}">
            <div class="card folder-card h-100 ${isSystem ? '' : 'custom-folder-card'}" 
                 style="border: 1px solid var(--border); cursor: pointer; background: ${safeGradient} !important; background-color: transparent !important;" 
                 onclick="openFolderDebounced('${folderId}')"
                 data-gradient="${escapeHtml(gradientColor)}"
                 data-folder-id="${folderId}">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="folder-icon-wrapper" style="width: 80px; height: 80px; position: relative;">
                            ${isSystem && folder.profile_image ? `
                                <div class="folder-doctor-image-container" style="width: 100%; height: 100%; border-radius: 50%; overflow: hidden; background: rgba(255, 255, 255, 0.25); display: flex; align-items: center; justify-content: center; border: 3px solid rgba(255, 255, 255, 0.4); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);">
                                    ${doctorImageHtml}
                                    <div class="folder-doctor-avatar-fallback" style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; color: white; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                        ${doctorInitial}
                                    </div>
                                </div>
                            ` : `
                                <div class="folder-icon-large" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.25); border-radius: 50%; border: 3px solid rgba(255, 255, 255, 0.4); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);">
                                    <i class="bi ${folderIcon}" style="font-size: ${isSystem ? '3rem' : '2rem'}; color: white; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);"></i>
                                </div>
                            `}
                        </div>
                        ${!isSystem ? `
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link p-0 text-white" 
                                        type="button" 
                                        data-bs-toggle="dropdown"
                                        onclick="event.stopPropagation();"
                                        style="opacity: 0.9; backdrop-filter: blur(4px);">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="event.stopPropagation(); showChangeFolderIconModal(${folder.id}, '${escapeHtml(folderIcon).replace(/'/g, "\\'")}', '${escapeHtml(gradientColor).replace(/'/g, "\\'")}');">
                                            <i class="bi bi-palette me-2"></i>
                                            Change Icon & Color
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="event.stopPropagation(); showRenameFolderModal(${folder.id}, '${escapeHtml(folder.name).replace(/'/g, "\\'")}');">
                                            <i class="bi bi-pencil me-2"></i>
                                            Rename
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); deleteFolder(${folder.id});">
                                            <i class="bi bi-trash me-2"></i>
                                            Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        ` : `
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link p-0 text-white" 
                                        type="button" 
                                        data-bs-toggle="dropdown"
                                        onclick="event.stopPropagation();"
                                        style="opacity: 0.9; backdrop-filter: blur(4px);">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="event.stopPropagation(); showChangeFolderIconModal('${folderId}', '${escapeHtml(folderIcon).replace(/'/g, "\\'")}', '${escapeHtml(gradientColor).replace(/'/g, "\\'")}');">
                                            <i class="bi bi-palette me-2"></i>
                                            Change Icon & Color
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        `}
                    </div>
                    <h6 class="card-title mb-2 text-white" style="font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        ${escapeHtml(folder.name)}
                    </h6>
                    <p class="text-white mb-2" style="opacity: 0.95; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                        <span class="badge bg-white text-black rounded-pill me-1 folder-patient-count" style="color:black !important;"><i class="bi bi-people me-1"></i> ${folder.patient_count || 0} patients</span> 
                        ${folder.sub_folders_count > 0 ? `<span class="badge bg-info text-white rounded-pill me-1 folder-patient-count" style="color:white !important;"><i class="bi bi-folder me-1"></i>${folder.sub_folders_count} sub-folders</span>` : ''}
                        ${isSystem ? `
                            <span class="badge bg-danger text-white rounded-pill me-1 folder-patient-count" style="color:white !important;">
                                <i class="bi bi-info-circle me-1"></i>
                                System folder
                            </span>
                        ` : ''} 
                    </p>         
                </div>
            </div>
        </div>
    `;
}

// Open folder and show patients
let currentFolderId = null;
let currentFolderName = null;
let currentFolderType = null; // 'system' or 'custom'
let currentFolderAbortController = null; // For request cancellation

// Folder path stack for breadcrumb tracking
let folderPathStack = []; // [{id, name, type}, ...]

// Multi-selection system
let selectedPatients = []; // Array of patient IDs
let selectedFolders = []; // Array of folder IDs
let selectionMode = false; // Whether selection mode is active

function openFolder(folderId) {
    // Return a Promise for async handling
    return new Promise((resolve, reject) => {
        // Cancel previous request
        if (currentFolderAbortController) {
            currentFolderAbortController.abort();
        }
        
        currentFolderAbortController = new AbortController();
        
        currentFolderId = folderId;

        // Determine folder type and get folder info. System folders use
        // either the `system_X` (doctor group) or `clinic_X` (clinic
        // group) prefix; anything else is a custom folder.
        const _fidStr = folderId.toString();
        const isSystem = _fidStr.startsWith('system_') || _fidStr.startsWith('clinic_');
        currentFolderType = isSystem ? 'system' : 'custom';

        // Get folder name from appropriate data source
        let folder = null;
        if (isSystem) {
            folder = systemFoldersData.find(f => f.id === folderId);
        } else {
            folder = customFoldersData.find(f => f.id === folderId);
        }

        if (!folder) {
            folder = foldersData.find(f => f.id === folderId);
        }

        currentFolderName = folder ? folder.name : 'Folder';

        // Note: Path stack will be updated from API breadcrumb response
        // This ensures consistency with backend data

        // Save state immediately for navigation tracking
        saveFolderNavigationState();
    
    // View toggle moved to unified filter bar - no need to hide header toggle
    
    // Hide Create Folder button when in system folder
    const createFolderBtn = document.querySelector('button[onclick="showCreateFolderModal()"]');
    if (createFolderBtn && isSystem) {
        createFolderBtn.style.display = 'none';
    }
    
    // Create header actions (Create Sub-folder + Group by buttons for system folders)
    const headerActions = isSystem ? `
        <div class="d-flex align-items-center gap-2 folder-header-actions-row">
            <button class="btn btn-sm create-subfolder-btn" onclick="showCreateSubFolderModal('${folderId}', '${currentFolderType}', '${escapeHtml(currentFolderName).replace(/'/g, "\\'")}')" title="Create Sub-folder">
                <i class="bi bi-folder-plus me-1"></i>
                <span class="btn-text-full">Create Sub-folder</span>
                <span class="btn-text-short d-none">Sub-folder</span>
            </button>
            <button class="btn btn-sm folder-action-btn" onclick="quickSortSystemFolder('${folderId}', 'by_date_created')" title="Group by Date Created">
                <i class="bi bi-calendar-event me-1"></i>
                <span class="btn-text-full">Group by Date Created</span>
                <span class="btn-text-short d-none">By Date</span>
            </button>
            <button class="btn btn-sm folder-action-btn" onclick="quickSortSystemFolder('${folderId}', 'by_visits')" title="Group by Visits">
                <i class="bi bi-calendar-check me-1"></i>
                <span class="btn-text-full">Group by Visits</span>
                <span class="btn-text-short d-none">By Visits</span>
            </button>
            <button class="btn btn-sm folder-action-btn" onclick="toggleSelectionMode(); renderFolderViewWithSelection();" title="Multi-select">
                <i class="bi bi-check-square me-1"></i>
                <span id="selectionModeLabel">Select</span>
            </button>
        </div>
    ` : `
        <div class="d-flex align-items-center gap-2 folder-header-actions-row">
            <button class="btn btn-sm create-subfolder-btn" onclick="showCreateSubFolderModal('${folderId}', '${currentFolderType}', '${escapeHtml(currentFolderName).replace(/'/g, "\\'")}')" title="Create Sub-folder">
                <i class="bi bi-folder-plus me-1"></i>
                <span class="btn-text-full">Create Sub-folder</span>
                <span class="btn-text-short d-none">Sub-folder</span>
            </button>
            <button class="btn btn-sm folder-action-btn" onclick="toggleSelectionMode(); renderFolderViewWithSelection();" title="Multi-select">
                <i class="bi bi-check-square me-1"></i>
                <span id="selectionModeLabel">Select</span>
            </button>
        </div>
    `;
    
    // Hide patientsFoldersContainer and show folderContentArea
    const patientsFoldersContainer = document.getElementById('patientsFoldersContainer');
    const folderContentArea = document.getElementById('folderContentArea');
    
    if (patientsFoldersContainer) {
        patientsFoldersContainer.style.display = 'none';
    }
    if (folderContentArea) {
        folderContentArea.style.display = 'block';
    }
    
    // Note: highlightActive will be called after data loads and treeview is expanded
    // This ensures the folder is visible in treeview before highlighting
    
    // Build breadcrumb HTML (will be updated when data loads)
    let breadcrumbHtml = `
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
                <li class="breadcrumb-item">
                    <a href="#" onclick="event.preventDefault(); renderFoldersView(); return false;" style="color: var(--accent); text-decoration: none;">
                        <i class="bi bi-folder me-1"></i>Folders
                    </a>
                </li>
            </ol>
        </nav>
    `;
    
    // Update folderContentArea elements
    const folderBreadcrumb = document.getElementById('folderBreadcrumb');
    const folderSearchContainer = document.getElementById('folderSearchContainer');
    const subFoldersContainer = document.getElementById('subFoldersContainer');
    const folderPatientsContainer = document.getElementById('folderPatientsContainer');
    
    if (folderBreadcrumb) {
        folderBreadcrumb.innerHTML = breadcrumbHtml;
    }
    
    if (folderSearchContainer) {
        folderSearchContainer.innerHTML = `
            <div class="folder-view-layout">
                <div class="folder-search-row mb-3 folder-search-container">
                    <label for="folderSearchInput" class="form-label small text-muted mb-2">
                        <i class="bi bi-search me-1"></i>Quick Search
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-alt); border-color: var(--border);">
                            <i class="bi bi-search" style="color: var(--accent);"></i>
                        </span>
                        <input type="text" 
                               id="folderSearchInput" 
                               class="form-control folder-search-input" 
                               placeholder="Search patients and folders by name, phone, or ID..."
                               autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="clearFolderSearch" style="display: none; border-color: var(--border);">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="folder-title-row mb-2">
                    <h5 class="mb-1" style="color: var(--text); font-weight: 600;">
                        ${escapeHtml(currentFolderName)}
                    </h5>
                    <small class="text-muted">Patients in this folder</small>
                </div>
                <div class="folder-actions-row">
                    ${headerActions}
                </div>
            </div>
        `;
    }
    
    if (subFoldersContainer) {
        subFoldersContainer.innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading sub-folders...</span>
                </div>
            </div>
        `;
    }
    
    if (folderPatientsContainer) {
        folderPatientsContainer.innerHTML = `
            <div class="col-12 text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
    }
    
    // Setup search functionality
    setupFolderSearch();

    // Update selection mode button
    updateSelectionModeButton();

    // Cache key for this folder
    const cacheKey = `folder_${folderId}`;

    // Load folder patients (API returns both patients AND sub-folders)
    fetch(`/api/patient-folders/${folderId}/patients`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        signal: currentFolderAbortController.signal
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Build breadcrumb - prioritize API breadcrumb, then use path stack, then build manually
            let breadcrumb = [];
            
            if (data.breadcrumb && Array.isArray(data.breadcrumb) && data.breadcrumb.length > 0) {
                // Use API breadcrumb and update path stack
                breadcrumb = data.breadcrumb;
                // Update path stack from API breadcrumb
                folderPathStack = breadcrumb.map(item => ({
                    id: item.id,
                    name: item.name,
                    type: item.type
                }));
                localStorage.setItem('folderPathStack', JSON.stringify(folderPathStack));
                
                // Expand folder path in treeview to make current folder visible
                if (folderTreeview) {
                    folderTreeview.expandFolderPath(folderPathStack).then(() => {
                        // After expanding path, expand current folder and all its children.
                        // folderId may already be prefixed (system_X / clinic_X) if it
                        // came from a treeview click; only wrap with `system_` when no
                        // prefix is present.
                        const _idStr = String(folderId);
                        const _hasPrefix = _idStr.startsWith('system_') || _idStr.startsWith('clinic_');
                        const currentFolderIdForTree = isSystem && !_hasPrefix
                            ? `system_${folderId}`
                            : _idStr;
                        folderTreeview.expandFolder(currentFolderIdForTree, true).then(() => {
                            folderTreeview.render();
                            folderTreeview.highlightActive(currentFolderIdForTree);
                        });
                    });
                }
            } else if (folderPathStack.length > 0) {
                // Use path stack if available
                breadcrumb = folderPathStack;
            } else {
                // Build breadcrumb manually as fallback
                if (isSystem) {
                    // System folder: Folders > Doctor Name
                    breadcrumb = [{
                        id: folderId,
                        name: currentFolderName,
                        type: 'system'
                    }];
                } else {
                    // Custom folder: check if it's a subfolder
                    let folder = null;
                    if (data.folders && data.folders.length > 0) {
                        folder = data.folders.find(f => f.id == folderId);
                    }
                    
                    if (!folder) {
                        folder = customFoldersData.find(f => f.id == folderId) || 
                                foldersData.find(f => f.id == folderId);
                    }
                    
                    const parentId = folder?.parent_id || folder?.parentId;
                    const parentType = folder?.parent_type || folder?.parentType || 'custom';
                    const parentName = folder?.parentName;
                    
                    if (parentId) {
                        // It's a subfolder
                        let parentFolder = null;
                        if (parentType === 'system') {
                            parentFolder = systemFoldersData.find(f => f.id === parentId);
                        } else {
                            parentFolder = customFoldersData.find(f => f.id == parentId) || 
                                         foldersData.find(f => f.id == parentId);
                        }
                        
                        if (parentFolder) {
                            breadcrumb = [
                                {
                                    id: parentFolder.id,
                                    name: parentFolder.name || parentName,
                                    type: parentType
                                },
                                {
                                    id: folderId,
                                    name: currentFolderName,
                                    type: 'custom'
                                }
                            ];
                        } else if (parentName) {
                            breadcrumb = [
                                {
                                    id: parentId,
                                    name: parentName,
                                    type: parentType
                                },
                                {
                                    id: folderId,
                                    name: currentFolderName,
                                    type: 'custom'
                                }
                            ];
                        } else {
                            breadcrumb = [{
                                id: folderId,
                                name: currentFolderName,
                                type: 'custom'
                            }];
                        }
                    } else {
                        // Top-level custom folder
                        breadcrumb = [{
                            id: folderId,
                            name: currentFolderName,
                            type: 'custom'
                        }];
                    }
                }
                
                // Update path stack from manually built breadcrumb
                folderPathStack = breadcrumb.map(item => ({
                    id: item.id,
                    name: item.name,
                    type: item.type
                }));
                localStorage.setItem('folderPathStack', JSON.stringify(folderPathStack));
                
                // Expand folder path in treeview to make current folder visible
                if (folderTreeview && breadcrumb.length > 0) {
                    folderTreeview.expandFolderPath(folderPathStack).then(() => {
                        const _idStr = String(folderId);
                        const _hasPrefix = _idStr.startsWith('system_') || _idStr.startsWith('clinic_');
                        const currentFolderIdForTree = isSystem && !_hasPrefix
                            ? `system_${folderId}`
                            : _idStr;
                        folderTreeview.expandFolder(currentFolderIdForTree, true).then(() => {
                            folderTreeview.render();
                            folderTreeview.highlightActive(currentFolderIdForTree);
                        });
                    });
                }
            }
            
            // Render breadcrumb
            if (breadcrumb && breadcrumb.length > 0) {
                renderFolderBreadcrumb(breadcrumb, folderId, currentFolderType);
            } else {
                // Render default breadcrumb with just current folder
                renderFolderBreadcrumb([{
                    id: folderId,
                    name: currentFolderName,
                    type: currentFolderType
                }], folderId, currentFolderType);
            }
            
            // Store data for search filtering
            currentFolderPatients = data.patients || [];
            currentFolderSubFolders = data.folders || [];
            
            // If there are sub-folders, render them
            const subFoldersContainer = document.getElementById('subFoldersContainer');
            if (data.folders && data.folders.length > 0) {
                if (subFoldersContainer) {
                    renderSubFolders(data.folders, folderId, currentFolderType);
                }
            } else {
                // Clear sub-folders container if no sub-folders
                if (subFoldersContainer) {
                    subFoldersContainer.innerHTML = '';
                }
            }
            
            // OLD FILTER MANAGER INIT REMOVED - Using UnifiedFilterManager instead
            
            // Render patients
            if (data.patients) {
                folderPaginationState.filteredPatients = data.patients;
                folderPaginationState.currentPage = 1;
                renderFolderPatients(data.patients);
                // Ensure pagination is rendered after DOM update
                setTimeout(() => {
                    renderFolderPaginationNav();
                    updateFolderPaginationInfo();
                }, 100);
            }
            
            // Apply current search filter if any
            const searchInput = document.getElementById('folderSearchInput');
            if (searchInput && searchInput.value.trim()) {
                filterFolderContent(searchInput.value.trim());
            }

            // Cache the data for performance
            folderCache.set(cacheKey, data);

            // Save navigation state after successful load
            saveFolderNavigationState();

            // Ensure treeview is highlighted after all operations complete
            if (folderTreeview) {
                const _idStr = String(folderId);
                const _hasPrefix = _idStr.startsWith('system_') || _idStr.startsWith('clinic_');
                const currentFolderIdForTree = isSystem && !_hasPrefix
                    ? `system_${folderId}`
                    : _idStr;
                // Use setTimeout to ensure this runs after all promises resolve
                setTimeout(() => {
                    folderTreeview.highlightActive(currentFolderIdForTree);
                }, 100);
            }

            // Resolve the Promise
            resolve(data);
        } else {
            reject(new Error('Failed to load folder data'));
        }
    })
    .catch(error => {
        if (error.name === 'AbortError') {
            return;
        }
        console.error('Error loading folder patients:', error);
        reject(error);
    });
    }); // End of Promise
}

// Setup folder search functionality
let folderSearchTimeout = null;
let currentFolderPatients = [];
let currentFolderSubFolders = [];

function setupFolderSearch() {
    const searchInput = document.getElementById('folderSearchInput');
    const clearBtn = document.getElementById('clearFolderSearch');
    
    if (!searchInput) return;
    
    // Clear previous timeout
    if (folderSearchTimeout) {
        clearTimeout(folderSearchTimeout);
    }
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();
        
        // Show/hide clear button
        if (clearBtn) {
            clearBtn.style.display = searchTerm ? 'block' : 'none';
        }
        
        // Debounce search
        if (folderSearchTimeout) {
            clearTimeout(folderSearchTimeout);
        }
        
        folderSearchTimeout = setTimeout(() => {
            filterFolderContent(searchTerm);
        }, 300);
    });
    
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            this.style.display = 'none';
            filterFolderContent('');
        });
    }
}

// Filter cards view content by search term
function filterCardsContent(searchTerm) {
    if (!searchTerm) {
        // Show all - restore original data
        paginationState.filteredPatients = [...paginationState.allPatients];
        renderPatientsCards();
        updatePaginationInfoCards();
        renderPaginationNavCards();
        
        // OLD FILTER MANAGER REMOVED - Using UnifiedFilterManager instead
        return;
    }
    
    const term = searchTerm.toLowerCase().trim();
    const filtered = paginationState.allPatients.filter(patient => {
        const firstName = (patient.first_name || '').toLowerCase();
        const lastName = (patient.last_name || '').toLowerCase();
        const fullName = `${firstName} ${lastName}`.trim();
        const phone = (patient.phone || '').toLowerCase();
        const id = patient.id ? patient.id.toString() : '';
        
        return fullName.includes(term) || 
               phone.includes(term) || 
               id.includes(term);
    });
    
    paginationState.filteredPatients = filtered;
    paginationState.currentPage = 1;
    
    // OLD FILTER MANAGER REMOVED - Using UnifiedFilterManager instead
    {
        renderPatientsCards();
        updatePaginationInfoCards();
        renderPaginationNavCards();
    }
}

// Cards quick search uses single input in card header (quickSearchCards) - see initializePagination()

function filterFolderContent(searchTerm) {
    if (!searchTerm) {
        // Show all - restore original data
        if (currentFolderPatients.length > 0) {
            renderFolderPatients(currentFolderPatients);
        }
        if (currentFolderSubFolders.length > 0) {
            const isSystem = currentFolderId && currentFolderId.toString().startsWith('system_');
            const parentType = isSystem ? 'system' : 'custom';
            renderSubFolders(currentFolderSubFolders, currentFolderId, parentType);
        }
        return;
    }
    
    // Filter patients
    const patientsContainer = document.getElementById('folderPatientsContainer');
    if (patientsContainer) {
        const filteredPatients = currentFolderPatients.filter(patient => {
            const fullName = `${patient.first_name || ''} ${patient.last_name || ''}`.toLowerCase();
            const phone = (patient.phone || '').toLowerCase();
            const nationalId = (patient.national_id || '').toLowerCase();
            
            return fullName.includes(searchTerm) || 
                   phone.includes(searchTerm) || 
                   nationalId.includes(searchTerm);
        });
        
        folderPaginationState.filteredPatients = filteredPatients;
        folderPaginationState.currentPage = 1;
        renderFolderPatients(filteredPatients);
        // Ensure pagination is rendered after DOM update
        setTimeout(() => {
            renderFolderPaginationNav();
            updateFolderPaginationInfo();
        }, 100);
    }
    
    // Filter subfolders
    const subFoldersContainer = document.getElementById('subFoldersContainer');
    if (subFoldersContainer) {
        const filteredSubFolders = currentFolderSubFolders.filter(subFolder => {
            const name = (subFolder.name || '').toLowerCase();
            return name.includes(searchTerm);
        });
        
        if (filteredSubFolders.length > 0) {
            const isSystem = currentFolderId && currentFolderId.toString().startsWith('system_');
            const parentType = isSystem ? 'system' : 'custom';
            renderSubFolders(filteredSubFolders, currentFolderId, parentType);
        } else {
            subFoldersContainer.innerHTML = '';
        }
    }
}

// Initialize lazy loading for patient images using Intersection Observer
let lazyImageObserver = null;

function initLazyLoading() {
    // Disconnect existing observer if any
    if (lazyImageObserver) {
        lazyImageObserver.disconnect();
    }
    
    // First, load images that are already in viewport immediately
    const lazyImages = document.querySelectorAll('img.lazy-load[data-src]');
    lazyImages.forEach(img => {
        // Check if image is in viewport
        const rect = img.getBoundingClientRect();
        const isInViewport = rect.top < window.innerHeight + 200 && rect.bottom > -200;
        
        if (isInViewport) {
            // Load immediately if in viewport
            const dataSrc = img.getAttribute('data-src');
            if (dataSrc) {
                img.src = dataSrc;
                img.removeAttribute('data-src');
                img.classList.remove('lazy-load');
                img.style.display = 'block';
            }
        }
    });
    
    // Create new Intersection Observer for images not yet in viewport
    lazyImageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                const dataSrc = img.getAttribute('data-src');
                
                if (dataSrc) {
                    // Load the image
                    img.src = dataSrc;
                    img.removeAttribute('data-src');
                    img.classList.remove('lazy-load');
                    img.style.display = 'block';
                    
                    // Stop observing this image
                    observer.unobserve(img);
                }
            }
        });
    }, {
        // Start loading when image is 200px before entering viewport
        rootMargin: '200px 0px',
        threshold: 0.01
    });
    
    // Observe remaining lazy-load images (those not in viewport)
    const remainingLazyImages = document.querySelectorAll('img.lazy-load[data-src]');
    remainingLazyImages.forEach(img => {
        lazyImageObserver.observe(img);
    });
}

// Load sub-folders for a parent folder
function loadSubFolders(parentId, parentType) {
    fetch(`/api/patient-folders/${parentId}/sub-folders/${parentType}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('subFoldersContainer');
        if (data.ok && data.sub_folders && data.sub_folders.length > 0) {
            // Store parent info in subfolders for breadcrumb
            data.sub_folders.forEach(subFolder => {
                subFolder.parentId = parentId;
                subFolder.parentType = parentType;
                // Find parent name
                if (parentType === 'system') {
                    const parentFolder = systemFoldersData.find(f => f.id === parentId);
                    if (parentFolder) {
                        subFolder.parentName = parentFolder.name;
                    }
                } else {
                    const parentFolder = customFoldersData.find(f => f.id == parentId) || foldersData.find(f => f.id == parentId);
                    if (parentFolder) {
                        subFolder.parentName = parentFolder.name;
                    }
                }
            });
            renderSubFolders(data.sub_folders, parentId, parentType);
        } else {
            if (container) {
                container.innerHTML = '';
            }
        }
    })
    .catch(error => {
        console.error('Error loading sub-folders:', error);
        const container = document.getElementById('subFoldersContainer');
        if (container) {
            container.innerHTML = '';
        }
    });
}

// Render sub-folders
function renderSubFolders(subFolders, parentId, parentType) {
    const container = document.getElementById('subFoldersContainer');
    
    // Check if container exists
    if (!container) {
        console.warn('subFoldersContainer not found in DOM');
        return;
    }
    
    if (subFolders.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    let html = `
        <div class="sub-folders-section mb-4">
            <h6 class="mb-3" style="color: var(--text); font-weight: 600;">
                <i class="bi bi-folder2-open me-2" style="color: var(--accent);"></i>
                Sub-folders
            </h6>
            <div class="row g-2">
    `;
    
    subFolders.forEach(subFolder => {
        const safeGradient = (subFolder.gradient_color || 'linear-gradient(135deg, #30cfd0 0%, #330867 100%)').replace(/'/g, "\\'");
        const subFolderIcon = subFolder.icon || 'bi-folder';
        
        // Store parent info in subfolder data for breadcrumb
        if (subFolder.parent_id) {
            subFolder.parentId = subFolder.parent_id;
            subFolder.parentType = subFolder.parent_type || 'custom';
        }
        
        html += `
            <div class="col-md-3 col-lg-2">
                <div class="card sub-folder-card h-100 ${selectionMode ? 'folder-card-selectable' : ''}" 
                     style="border: 1px solid var(--border); cursor: pointer; background: ${safeGradient} !important; background-color: transparent !important; position: relative;" 
                     onclick="if (!selectionMode) openSubFolder(${subFolder.id}, '${parentId}', '${parentType}')">
                    ${selectionMode ? `
                        <div class="position-absolute top-0 start-0 m-2" style="z-index: 10;" onclick="event.stopPropagation();">
                            <input type="checkbox" 
                                   class="form-check-input folder-select-checkbox" 
                                   data-folder-id="${subFolder.id}"
                                   ${selectedFolders.includes(subFolder.id.toString()) ? 'checked' : ''}
                                   onchange="toggleFolderSelection('${subFolder.id}')"
                                   onclick="event.stopPropagation();"
                                   style="width: 1.25rem; height: 1.25rem; cursor: pointer; background-color: white;">
                        </div>
                    ` : ''}
                    <div class="card-body p-3 d-flex flex-column align-items-center text-center position-relative">
                        <div class="dropdown position-absolute" style="top: 0.5rem; right: 0.5rem; z-index: 10;" onclick="event.stopPropagation();">
                            <button class="btn btn-sm btn-link p-0 text-white" 
                                    type="button" 
                                    data-bs-toggle="dropdown"
                                    style="opacity: 0.9; backdrop-filter: blur(4px);">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#" onclick="event.stopPropagation(); showChangeFolderIconModal(${subFolder.id}, '${escapeHtml(subFolderIcon).replace(/'/g, "\\'")}', '${escapeHtml(subFolder.gradient_color || '').replace(/'/g, "\\'")}');">
                                        <i class="bi bi-palette me-2"></i>
                                        Change Icon & Color
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="event.stopPropagation(); showRenameFolderModal(${subFolder.id}, '${escapeHtml(subFolder.name).replace(/'/g, "\\'")}');">
                                        <i class="bi bi-pencil me-2"></i>
                                        Rename
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); deleteFolder(${subFolder.id});">
                                        <i class="bi bi-trash me-2"></i>
                                        Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="mb-2">
                            <i class="bi ${subFolderIcon}" style="font-size: 2rem; color: white; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);"></i>
                        </div>
                        <h6 class="card-title mb-1 text-white" style="font-size: 0.85rem; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                            ${escapeHtml(subFolder.name)}
                        </h6>
                        <small class="text-white" style="opacity: 0.95; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                            <span class="badge bg-white text-black rounded-pill me-1 folder-patient-count" style="color:black !important"><i class="bi bi-people me-1"></i> ${subFolder.patient_count || 0} patients</span>
                        </small>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += `
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

// Render folder breadcrumb
function renderFolderBreadcrumb(breadcrumb, currentFolderId, currentFolderType) {
    const breadcrumbContainer = document.getElementById('folderBreadcrumb');
    if (!breadcrumbContainer) {
        return;
    }
    
    if (!breadcrumb || !Array.isArray(breadcrumb) || breadcrumb.length === 0) {
        return;
    }
    
    let html = `
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
                <li class="breadcrumb-item">
                    <a href="#" onclick="event.preventDefault(); renderFoldersView(); return false;" style="color: var(--accent); text-decoration: none;">
                        <i class="bi bi-folder me-1"></i>Folders
                    </a>
                </li>
    `;
    
    // Add breadcrumb items
    breadcrumb.forEach((item, index) => {
        if (!item || !item.id || !item.name) {
            return;
        }
        
        const isLast = index === breadcrumb.length - 1;
        const itemId = item.id ? item.id.toString() : '';
        const currentId = currentFolderId ? currentFolderId.toString() : '';
        
        if (isLast || itemId === currentId) {
            // Current folder - not clickable
            html += `
                <li class="breadcrumb-item active" aria-current="page">
                    ${escapeHtml(item.name)}
                </li>
            `;
        } else {
            // Parent folder - clickable
            const folderIdToOpen = item.type === 'system' ? item.id : item.id;
            html += `
                <li class="breadcrumb-item">
                    <a href="#" onclick="event.preventDefault(); openFolderDebounced('${folderIdToOpen}'); return false;" style="color: var(--accent); text-decoration: none;">
                        ${escapeHtml(item.name)}
                    </a>
                </li>
            `;
        }
    });
    
    html += `
            </ol>
        </nav>
    `;
    
    breadcrumbContainer.innerHTML = html;
}

// Open sub-folder
function openSubFolder(subFolderId, parentId = null, parentType = null) {
    // Note: Path stack will be updated from API breadcrumb response
    // This ensures consistency with backend data

    // Use debounced version to prevent rapid click issues
    openFolderDebounced(subFolderId.toString());
}

// Show create sub-folder modal
function showCreateSubFolderModal(parentId, parentType, parentName) {
    const modal = new bootstrap.Modal(document.getElementById('createSubFolderModal'));
    document.getElementById('subFolderParentId').value = parentId;
    document.getElementById('subFolderParentType').value = parentType;
    document.getElementById('subFolderParentName').textContent = parentName;
    document.getElementById('subFolderName').value = '';
    document.getElementById('createSubFolderMessage').classList.add('d-none');
    modal.show();
}

// Quick sort system folder
function quickSortSystemFolder(systemFolderId, sortType) {
    const sortTypeName = sortType === 'by_date_created' ? 'Date Created' : 'Visits';
    showConfirmModal(
        'Group Patients',
        `Are you sure you want to group patients by ${sortTypeName}? This will create sub-folders automatically.`,
        function() {
            performQuickSort(systemFolderId, sortType);
        }
    );
}

function performQuickSort(systemFolderId, sortType) {
    // Show loading
    const container = document.getElementById('subFoldersContainer');
    const originalContent = container.innerHTML;
    container.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Sorting patients...</span>
            </div>
            <p class="mt-2 text-muted">Sorting patients into sub-folders...</p>
        </div>
    `;
    
    fetch(`/api/patient-folders/${systemFolderId}/quick-sort/${sortType}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            showNotification(`Patients sorted successfully! ${data.patients_distributed} patients distributed into ${data.sub_folders_created.length} sub-folders.`, 'success');
            // Refresh the full folder view (sub-folders + patients) via API
            openFolder(systemFolderId);
            // Also refresh patients data for cards/table views
            refreshPatientsData();
        } else {
            showNotification(data.error || 'Failed to sort patients', 'error');
            container.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Error sorting patients:', error);
        showNotification('An error occurred while sorting patients', 'error');
        container.innerHTML = originalContent;
    });
}

// Card size management for folders view
function setCardSize(size) {
    localStorage.setItem('folderCardSize', size);
    
    // Update button states
    document.querySelectorAll('.card-size-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-size') === size) {
            btn.classList.add('active');
        }
    });
    
    // Re-render patients if folder is open
    if (currentFolderPatients && currentFolderPatients.length > 0) {
        renderFolderPatients(currentFolderPatients);
    }
}

// Card size management for cards view
function setCardSizeCards(size) {
    localStorage.setItem('cardsViewCardSize', size);
    
    // Update button states
    document.querySelectorAll('.card-size-btn-cards').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-size') === size) {
            btn.classList.add('active');
        }
    });
    
    // Re-render patients cards
    if (currentViewMode === 'cards') {
        renderPatientsCards();
    }
}

// Initialize card size for cards view on load
function initCardSizeCards() {
    const savedSize = localStorage.getItem('cardsViewCardSize') || 'small';
    setCardSizeCards(savedSize);
}

// Initialize card size on load
function initCardSize() {
    const savedSize = localStorage.getItem('folderCardSize') || 'small';
    setCardSize(savedSize);
}

// Render patients in a folder (styled like cards view)
function renderFolderPatients(patients) {
    const container = document.getElementById('folderPatientsContainer');
    
    if (!container) {
        console.warn('folderPatientsContainer not found in DOM');
        return;
    }
    
    // Update pagination state
    folderPaginationState.filteredPatients = patients;
    folderPaginationState.totalItems = patients.length;
    
    if (patients.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No patients in this folder</p>
            </div>
        `;
        // Clear pagination
        const paginationContainer = document.getElementById('folderPaginationContainer');
        if (paginationContainer) {
            paginationContainer.innerHTML = '';
        }
        return;
    }
    
    // Calculate pagination
    const { currentPage, itemsPerPage } = folderPaginationState;
    let startIndex, endIndex, patientsToShow;
    
    if (itemsPerPage === 'all' || itemsPerPage >= patients.length) {
        patientsToShow = patients;
    } else {
        startIndex = (currentPage - 1) * itemsPerPage;
        endIndex = Math.min(startIndex + itemsPerPage, patients.length);
        patientsToShow = patients.slice(startIndex, endIndex);
    }
    
    // Get card size from localStorage
    const cardSize = localStorage.getItem('folderCardSize') || 'small';
    const sizeClass = {
        small: 'col-md-6 col-lg-4 col-xl-3',
        medium: 'col-md-4 col-lg-3',
        large: 'col-md-3 col-lg-2'
    }[cardSize] || 'col-md-6 col-lg-4 col-xl-3';
    
    let html = '';
    
    patientsToShow.forEach(patient => {
        const age = patient.dob ? calculateAge(patient.dob) : null;
        const ageText = age !== null ? `${age} years` : 'Not specified';
        const lastAppointment = patient.last_appointment_datetime 
            ? formatDate(patient.last_appointment_datetime) 
            : (patient.last_visit ? formatDate(patient.last_visit) : 'Not visited yet');
        
        const firstName = patient.first_name || '';
        const lastName = patient.last_name || '';
        const fullName = `${firstName} ${lastName}`.trim();
        
        // Get patient image URL (using dedicated endpoint for cards/folders views)
        const imageUrl = patient.latest_attachment_id 
            ? `/api/patients/images/${patient.latest_attachment_id}`
            : null;
        
        // Gender icon
        const genderIcon = patient.gender === 'Female' 
            ? '<i class="bi bi-gender-female"></i>' 
            : (patient.gender === 'Male' 
                ? '<i class="bi bi-gender-male"></i>' 
                : '<i class="bi bi-gender-ambiguous"></i>');
        
        const genderBadgeClass = patient.gender === 'Female' 
            ? 'bg-pink' 
            : (patient.gender === 'Male' ? 'bg-primary' : 'bg-secondary');
        
        // Check if patient is in a system folder (cannot remove from system folders)
        const isSystemFolder = currentFolderId && currentFolderId.toString().startsWith('system_');
        
        // Get color marker (will be fetched and cached)
        const colorMarker = patient.color_marker || null;
        
        html += `
            <div class="${sizeClass} mb-3">
                <div class="card patient-card clickable h-100 ${colorMarker ? 'patient-card-has-marker' : ''} ${selectionMode ? 'patient-card-selectable' : ''}" 
                     data-patient-id="${patient.id}"
                     data-has-color-marker="${colorMarker ? 'true' : 'false'}"
                     style="${colorMarker ? `--marker-color: ${colorMarker}; --marker-color-rgb: ${hexToRgb(colorMarker)};` : ''} border: ${colorMarker ? `2px solid ${colorMarker}` : '1px solid var(--border)'}; cursor: pointer; position: relative;" 
                     onclick="if (!selectionMode) viewPatient(${patient.id})">
                    ${selectionMode ? `
                        <div class="position-absolute top-0 start-0 m-2" style="z-index: 10;" onclick="event.stopPropagation();">
                            <input type="checkbox" 
                                   class="form-check-input patient-select-checkbox" 
                                   data-patient-id="${patient.id}"
                                   ${selectedPatients.includes(patient.id) ? 'checked' : ''}
                                   onchange="togglePatientSelection(${patient.id})"
                                   onclick="event.stopPropagation();"
                                   style="width: 1.25rem; height: 1.25rem; cursor: pointer;">
                        </div>
                    ` : ''}
                    ${colorMarker ? `
                        <div class="patient-color-marker" 
                             style="position: absolute; top: 8px; right: 8px; width: 12px; height: 12px; border-radius: 50%; background: ${colorMarker}; border: 2px solid white; z-index: 5; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"
                             onclick="event.stopPropagation(); showColorMarkerModal(${patient.id}, '${colorMarker}')"
                             title="Click to change color marker"></div>
                    ` : `
                        <div class="patient-color-marker-add" 
                             onclick="event.stopPropagation(); showColorMarkerModal(${patient.id}, null)"
                             title="Click to add color marker">
                            <i class="bi bi-plus-lg"></i>
                        </div>
                    `}
                    <!-- Patient Image -->
                    <div class="position-relative patient-card-image-container" style="height: 200px; overflow: hidden; background: var(--bg-alt);">
                        ${imageUrl ? `
                            <img data-src="${imageUrl}" 
                                 alt="${escapeHtml(fullName)}" 
                                 class="w-100 h-100 patient-card-image lazy-load" 
                                 style="object-fit: cover; opacity: 0; transition: opacity 0.3s ease;"
                                 data-patient-id="${patient.id}"
                                 data-image-url="${imageUrl}"
                                 onerror="(function(img, url, pid) { img.style.display='none'; img.style.opacity='0'; const placeholder = img.nextElementSibling; if (placeholder) { placeholder.style.display='flex'; } })(this, '${imageUrl}', ${patient.id});"
                                 onload="(function(img) { img.style.opacity='1'; img.style.display='block'; const placeholder = img.nextElementSibling; if (placeholder) { placeholder.style.display='none'; } })(this);">
                        ` : ''}
                        <div class="d-flex align-items-center justify-content-center h-100 w-100 patient-card-placeholder" 
                             style="background: linear-gradient(135deg, var(--accent) 0%, var(--bg-alt) 100%); ${imageUrl ? 'display: none;' : 'display: flex;'}">
                            <i class="bi bi-person-circle text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <!-- Badges overlay -->
                        <div class="position-absolute top-0 start-0 p-2 d-flex gap-2 flex-wrap">
                            ${age !== null ? `
                                <span class="badge bg-info">${age} years</span>
                            ` : ''}
                            <span class="badge ${genderBadgeClass}">${genderIcon}</span>
                        </div>
                        ${patient.last_clinic_id ? `
                            <!-- Last visit clinic badge (top-right) -->
                            <div class="position-absolute p-2" style="top: ${colorMarker ? '30px' : '0'}; right: 0;">
                                ${renderClinicBadge(patient, { compact: true })}
                            </div>
                        ` : ''}
                        <!-- Treatment Doctor Badge -->
                        ${patient.created_by_doctor_name ? `
                            <div class="position-absolute bottom-0 start-0 p-2">
                                <span class="badge bg-primary" style="font-size: 0.7rem; backdrop-filter: blur(4px); background: rgba(14, 165, 233, 0.9) !important; border: 1px solid rgba(255, 255, 255, 0.3);">
                                    TD: ${escapeHtml(patient.created_by_doctor_name)}
                                </span>
                            </div>
                        ` : ''}
                    </div>
                    
                    <!-- Card Body -->
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title mb-2">
                            <span style="color: var(--accent); font-weight: 600;">
                                ${escapeHtml(fullName)}
                            </span>
                        </h6>
                        
                        <div class="mb-2">
                            ${patient.phone ? `
                                <small class="text-muted d-block">
                                    <i class="bi bi-telephone me-1"></i>
                                    <div class="phone-number-container" style="position: relative; display: inline-block;" onclick="event.stopPropagation();">
                                        <a href="tel:${escapeHtml(patient.phone)}" 
                                           class="phone-number-link" 
                                           style="text-decoration: none; color: var(--accent); font-weight: 500; cursor: pointer; transition: all 0.2s ease;">
                                            ${escapeHtml(patient.phone)}
                                        </a>
                                        <span class="phone-htooltip">
                                            <div class="phone-actions">
                                                <a href="tel:${escapeHtml(patient.phone)}" class="phone-action-btn" title="Call" onclick="event.stopPropagation();">
                                                    <i class="bi bi-telephone-fill"></i>
                                                    <span>Call</span>
                                                </a>
                                                <a href="https://wa.me/+2${escapeHtml(patient.phone).replace(/[^0-9]/g, '')}" target="_blank" class="phone-action-btn whatsapp-btn" title="WhatsApp" onclick="event.stopPropagation();">
                                                    <i class="bi bi-whatsapp"></i>
                                                    <span>WhatsApp</span>
                                                </a>
                                            </div>
                                        </span>
                                    </div>
                                </small>
                            ` : ''}
                        </div>
                        
                        <div class="mt-auto pt-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    ${patient.total_appointments || 0} visits
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    ${lastAppointment}
                                </small>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-capsule me-1"></i>
                                    ${patient.prescriptions_count || 0} prescriptions
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-eyeglasses me-1"></i>
                                    ${patient.glasses_count || 0} glasses
                                </small>
                            </div>
                            
                            <!-- Patient Tags -->
                            <div class="mb-2" id="patientTags_${patient.id}"></div>
                            
                            <!-- Quick Actions -->
                            <div class="d-flex gap-1 justify-content-end mt-2 pt-2 border-top" onclick="event.stopPropagation();">
                                <a href="/doctor/patients/${patient.id}" 
                                   class="card-action-btn card-action-view" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   data-bs-title="View patient"
                                   onclick="event.stopPropagation();">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="card-action-btn card-action-book" 
                                        onclick="event.stopPropagation(); bookAppointment(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Book appointment">
                                    <i class="bi bi-calendar-plus"></i>
                                </button>
                                <button class="card-action-btn card-action-edit" 
                                        onclick="event.stopPropagation(); editPatient(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Edit patient">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="card-action-btn card-action-move" 
                                        onclick="event.stopPropagation(); showMovePatientModal(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Move to folder">
                                    <i class="bi bi-folder"></i>
                                </button>
                                <button class="card-action-btn card-action-add" 
                                        onclick="event.stopPropagation(); showAddToFolderModal(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Add to folder"
                                        style="background: var(--success, #28a745); color: white;">
                                    <i class="bi bi-folder-plus"></i>
                                </button>
                                ${!isSystemFolder && currentFolderId ? `
                                    <button class="card-action-btn card-action-remove" 
                                            onclick="event.stopPropagation(); removePatientFromFolder(${patient.id})" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="Remove from folder"
                                            style="background: var(--danger, #dc3545); color: white;">
                                        <i class="bi bi-folder-minus"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    // Use DocumentFragment for better performance
    const fragment = document.createDocumentFragment();
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    
    while (tempDiv.firstChild) {
        fragment.appendChild(tempDiv.firstChild);
    }
    
    container.innerHTML = '';
    container.appendChild(fragment);
    
    // Initialize lazy loading for images
    initLazyLoading();
    
    // Refresh tooltips
    setTimeout(() => {
        refreshTooltips();
    }, 100);
    
    // Fetch color markers for all patients (only for current page)
    fetchColorMarkersForPatients(patientsToShow);
    
    // Fetch tags for all patients (only for current page)
    fetchTagsForPatients(patientsToShow);
    
    // Update per page selector
    const perPageSelector = document.getElementById('folderItemsPerPageSelector');
    if (perPageSelector) {
        const { itemsPerPage } = folderPaginationState;
        perPageSelector.value = (itemsPerPage === 'all' || itemsPerPage >= patients.length) ? 'all' : itemsPerPage.toString();
    }
    
    // Render pagination (use setTimeout to ensure DOM is ready)
    setTimeout(() => {
        renderFolderPaginationNav();
        updateFolderPaginationInfo();
    }, 100);
}

// Fetch color markers for patients (BATCH API - reduces N calls to 1)
async function fetchColorMarkersForPatients(patients) {
    if (!patients || patients.length === 0) return;

    try {
        const patientIds = patients.map(p => p.id);

        const response = await fetch('/api/patient-color-markers/batch', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ patient_ids: patientIds })
        });

        const data = await response.json();

        if (data.ok && data.markers) {
            // Update all patients from batch response
            patients.forEach(patient => {
                const colorCode = data.markers[patient.id] || null;
                patient.color_marker = colorCode;

                // Update DOM
                const card = document.querySelector(`[data-patient-id="${patient.id}"]`);
                if (card) {
                    updateCardColorMarker(card, patient.id, colorCode);
                }
            });
        }
    } catch (error) {
        console.error('Error fetching color markers batch:', error);
    }
}

// Helper function to update card color marker DOM
function updateCardColorMarker(card, patientId, colorCode) {
    const marker = card.querySelector('.patient-color-marker');
    const markerAdd = card.querySelector('.patient-color-marker-add');

    if (colorCode) {
        if (markerAdd) markerAdd.remove();
        if (!marker) {
            const markerDiv = document.createElement('div');
            markerDiv.className = 'patient-color-marker';
            markerDiv.style.cssText = 'position: absolute; top: 8px; right: 8px; width: 12px; height: 12px; border-radius: 50%; background: ' + colorCode + '; border: 2px solid white; z-index: 5; box-shadow: 0 2px 4px rgba(0,0,0,0.2);';
            markerDiv.setAttribute('onclick', `event.stopPropagation(); showColorMarkerModal(${patientId}, '${colorCode}')`);
            markerDiv.setAttribute('title', 'Click to change color marker');
            card.appendChild(markerDiv);
        } else {
            marker.style.background = colorCode;
            marker.setAttribute('onclick', `event.stopPropagation(); showColorMarkerModal(${patientId}, '${colorCode}')`);
        }

        // Update card border and glow
        card.setAttribute('data-has-color-marker', 'true');
        card.classList.add('patient-card-has-marker');
        card.style.setProperty('--marker-color', colorCode);
        card.style.setProperty('--marker-color-rgb', hexToRgb(colorCode));
        card.style.border = `2px solid ${colorCode}`;
    } else {
        if (marker) marker.remove();
        if (!markerAdd) {
            const markerAddDiv = document.createElement('div');
            markerAddDiv.className = 'patient-color-marker-add';
            markerAddDiv.setAttribute('onclick', `event.stopPropagation(); showColorMarkerModal(${patientId}, null)`);
            markerAddDiv.setAttribute('title', 'Click to add color marker');
            markerAddDiv.innerHTML = '<i class="bi bi-plus-lg"></i>';
            card.appendChild(markerAddDiv);
        }
        // Remove card styling
        card.setAttribute('data-has-color-marker', 'false');
        card.classList.remove('patient-card-has-marker');
        card.style.removeProperty('--marker-color');
        card.style.removeProperty('--marker-color-rgb');
        card.style.border = '1px solid var(--border)';
    }
}

// Fetch color markers for table view patients (BATCH API - reduces N calls to 1)
async function fetchColorMarkersForTablePatients(patients) {
    if (!patients || patients.length === 0) return;

    try {
        const patientIds = patients.map(p => p.id);

        const response = await fetch('/api/patient-color-markers/batch', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ patient_ids: patientIds })
        });

        const data = await response.json();

        if (data.ok && data.markers) {
            // Update all patients from batch response
            patients.forEach(patient => {
                const colorCode = data.markers[patient.id] || null;
                patient.color_marker = colorCode;
                updateTableRowMarker(patient.id, colorCode);
            });
        }
    } catch (error) {
        console.error('Error fetching color markers batch for table:', error);
    }
}

// Update color marker in table row
function updateTableRowMarker(patientId, colorMarker) {
    const row = document.querySelector(`tr[data-patient-id="${patientId}"]`);
    if (!row) return;
    
    // Find the first td (Patient Info column)
    const firstTd = row.querySelector('td:first-child');
    if (!firstTd) return;
    
    // Find the flex container
    const flexContainer = firstTd.querySelector('.d-flex.align-items-center');
    if (!flexContainer) return;
    
    // Find the marker container within the first td
    let markerContainer = firstTd.querySelector('.patient-info-marker-container');
    
    // If container doesn't exist, create it at the beginning of the flex container
    if (!markerContainer) {
        markerContainer = document.createElement('div');
        markerContainer.className = 'patient-info-marker-container';
        markerContainer.style.cssText = 'flex-shrink: 0; width: 12px; height: 12px; margin-right: 8px;';
        // Insert at the beginning of flex container (before avatar)
        const avatar = flexContainer.querySelector('.avatar-circle');
        if (avatar) {
            flexContainer.insertBefore(markerContainer, avatar);
        } else {
            flexContainer.insertBefore(markerContainer, flexContainer.firstChild);
        }
    } else {
        // Ensure container has correct styling
        markerContainer.style.cssText = 'flex-shrink: 0; width: 12px; height: 12px; margin-right: 8px;';
    }
    
    // Remove any existing markers outside the container (cleanup)
    // Clean up ALL markers in the entire row that are not inside the markerContainer
    row.querySelectorAll('.patient-color-marker, .patient-color-marker-table, .patient-color-marker-add-table').forEach(el => {
        if (!markerContainer.contains(el)) {
            el.remove();
        }
    });
    
    if (colorMarker) {
        markerContainer.innerHTML = `
            <div class="patient-color-marker-table me-2" 
                 style="width: 12px; height: 12px; border-radius: 50%; background: ${colorMarker}; cursor: pointer; flex-shrink: 0;"
                 onclick="event.stopPropagation(); showColorMarkerModal(${patientId}, '${colorMarker}')"
                 title="Click to change color marker"></div>
        `;
    } else {
        markerContainer.innerHTML = `
            <button class="btn btn-sm btn-link p-0 me-2 patient-color-marker-add-table" 
                    style="width: 12px; height: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; padding: 0; opacity: 0; transition: opacity 0.2s ease;"
                    onmouseenter="this.style.opacity='1'"
                    onmouseleave="this.style.opacity='0'"
                    onclick="event.stopPropagation(); showColorMarkerModal(${patientId}, null)"
                    title="Click to add color marker">
                <i class="bi bi-plus-lg" style="font-size: 0.6rem;"></i>
            </button>
        `;
        
        // Add hover effect to show add marker button on row hover
        row.addEventListener('mouseenter', function() {
            const addBtn = this.querySelector('.patient-color-marker-add-table');
            if (addBtn) {
                addBtn.style.opacity = '1';
            }
        });
        
        row.addEventListener('mouseleave', function() {
            const addBtn = this.querySelector('.patient-color-marker-add-table');
            if (addBtn) {
                addBtn.style.opacity = '0';
            }
        });
    }
}

// Fetch tags for table view patients (BATCH API - reduces N calls to 1)
async function fetchTagsForTablePatients(patients) {
    if (!patients || patients.length === 0) return;

    try {
        const patientIds = patients.map(p => p.id);

        const response = await fetch('/api/patients/tags/batch', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ patient_ids: patientIds })
        });

        const data = await response.json();

        if (data.ok && data.tags) {
            // Update all patients from batch response
            patients.forEach(patient => {
                const tags = data.tags[patient.id] || [];
                patient.tags = tags;

                // Update tags in table row
                const tagsContainer = document.getElementById(`patientTagsTable_${patient.id}`);
                if (tagsContainer) {
                    let tagsHtml = '';
                    if (tags.length > 0) {
                        tagsHtml = tags.map(tag => `
                            <span class="badge patient-tag"
                                  style="background: ${tag.color || '#6366f1'}; font-size: 0.7rem; padding: 0.25rem 0.5rem; cursor: pointer;"
                                  onclick="event.stopPropagation(); removeTagFromPatient(${patient.id}, ${tag.id})"
                                  title="Click to remove tag">
                                ${tag.icon ? `<i class="bi ${tag.icon} me-1"></i>` : ''}
                                ${escapeHtml(tag.name)}
                            </span>
                        `).join('');
                    }
                    tagsHtml += `
                        <button class="btn btn-sm btn-link p-0 add-tag-btn-table"
                                style="font-size: 0.7rem; color: var(--text); padding: 0;"
                                onclick="event.stopPropagation(); showTagManagementModal(${patient.id})"
                                title="Add tag">
                            <i class="bi bi-plus-circle me-1"></i>Add
                        </button>
                    `;
                    tagsContainer.innerHTML = tagsHtml;
                }
            });
        }
    } catch (error) {
        console.error('Error fetching tags batch for table:', error);
    }
}

// Show color marker modal
function showColorMarkerModal(patientId, currentColor) {
    // Create modal HTML if it doesn't exist
    let modal = document.getElementById('colorMarkerModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'colorMarkerModal';
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-palette me-2"></i>
                            Color Marker
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Select a color marker for this patient</p>
                        <div class="color-picker-grid" id="colorPickerGrid">
                            <!-- Colors will be added here -->
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="removeColorMarkerBtn" style="display: none;">
                                <i class="bi bi-trash me-1"></i>
                                Remove Color Marker
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    // Define colors
    const colors = [
        { code: '#ef4444', name: 'Red' },
        { code: '#f59e0b', name: 'Orange' },
        { code: '#eab308', name: 'Yellow' },
        { code: '#22c55e', name: 'Green' },
        { code: '#06b6d4', name: 'Cyan' },
        { code: '#3b82f6', name: 'Blue' },
        { code: '#8b5cf6', name: 'Purple' },
        { code: '#ec4899', name: 'Pink' }
    ];
    
    // Build color grid
    const grid = modal.querySelector('#colorPickerGrid');
    grid.innerHTML = '';
    grid.style.cssText = 'display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;';
    
    colors.forEach(color => {
        const colorBtn = document.createElement('button');
        colorBtn.type = 'button';
        colorBtn.className = 'btn color-picker-btn';
        colorBtn.style.cssText = `width: 100%; height: 60px; background: ${color.code}; border: 3px solid ${currentColor === color.code ? '#000' : 'transparent'}; border-radius: 8px; position: relative;`;
        colorBtn.setAttribute('data-color', color.code);
        colorBtn.setAttribute('title', color.name);
        if (currentColor === color.code) {
            colorBtn.innerHTML = '<i class="bi bi-check-circle-fill text-white" style="font-size: 1.5rem;"></i>';
        }
        colorBtn.addEventListener('click', () => {
            updatePatientColorMarker(patientId, color.code);
            bootstrap.Modal.getInstance(modal).hide();
        });
        grid.appendChild(colorBtn);
    });
    
    // Show/hide remove button
    const removeBtn = modal.querySelector('#removeColorMarkerBtn');
    if (currentColor) {
        removeBtn.style.display = 'block';
        removeBtn.onclick = () => {
            updatePatientColorMarker(patientId, null);
            bootstrap.Modal.getInstance(modal).hide();
        };
    } else {
        removeBtn.style.display = 'none';
    }
    
    // Show modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

// Update patient color marker
function updatePatientColorMarker(patientId, colorCode) {
    if (colorCode === null) {
        // Delete color marker
        fetch(`/api/patient-color-markers/${patientId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Update patient object
                const patient = currentFolderPatients.find(p => p.id === patientId);
                if (patient) {
                    patient.color_marker = null;
                }
                // Update DOM - check if it's a card (cards/folders view) or table row
                // Skip patient profile header (it has its own marker button)
                const header = document.getElementById('patientProfileHeader');
                const headerPatientId = header ? header.getAttribute('data-patient-id') : null;
                
                // If this is the patient profile header, skip DOM update (handled by patient.js)
                if (headerPatientId && parseInt(headerPatientId) === parseInt(patientId)) {
                    // Header is handled by patient.js, just return
                    return;
                }
                
                const element = document.querySelector(`[data-patient-id="${patientId}"]`);
                if (element) {
                    // Check if it's a table row (tr) or a card (div)
                    if (element.tagName === 'TR') {
                        // It's a table row - use updateTableRowMarker
                        updateTableRowMarker(patientId, null);
                    } else if (element.id !== 'patientProfileHeader') {
                        // It's a card (cards/folders view) - but not the header
                        const marker = element.querySelector('.patient-color-marker');
                        if (marker) {
                            marker.remove();
                            const markerAddDiv = document.createElement('div');
                            markerAddDiv.className = 'patient-color-marker-add';
                            markerAddDiv.setAttribute('onclick', `event.stopPropagation(); showColorMarkerModal(${patientId}, null)`);
                            markerAddDiv.setAttribute('title', 'Click to add color marker');
                            markerAddDiv.innerHTML = '<i class="bi bi-plus-lg"></i>';
                            element.appendChild(markerAddDiv);
                        }
                        
                        // Remove card border and glow styling
                        element.setAttribute('data-has-color-marker', 'false');
                        element.classList.remove('patient-card-has-marker');
                        element.style.removeProperty('--marker-color');
                        element.style.removeProperty('--marker-color-rgb');
                        element.style.border = '1px solid var(--border)';
                    }
                } else {
                    // Element not found, try to update table row anyway
                    updateTableRowMarker(patientId, null);
                }
                
                showNotification('Color marker removed', 'success');
            }
        })
        .catch(error => {
            console.error('Error removing color marker:', error);
            showNotification('Failed to remove color marker', 'error');
        });
    } else {
        // Update/create color marker
        fetch(`/api/patient-color-markers/${patientId}`, {
            method: 'PUT',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ color_code: colorCode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Update patient object
                const patient = currentFolderPatients.find(p => p.id === patientId);
                if (patient) {
                    patient.color_marker = colorCode;
                }
                // Update DOM - check if it's a card (cards/folders view) or table row
                // Skip patient profile header (it has its own marker button)
                const header = document.getElementById('patientProfileHeader');
                const headerPatientId = header ? header.getAttribute('data-patient-id') : null;
                
                // If this is the patient profile header, skip DOM update (handled by patient.js)
                if (headerPatientId && parseInt(headerPatientId) === parseInt(patientId)) {
                    // Header is handled by patient.js, just return
                    return;
                }
                
                const element = document.querySelector(`[data-patient-id="${patientId}"]`);
                if (element) {
                    // Check if it's a table row (tr) or a card (div)
                    if (element.tagName === 'TR') {
                        // It's a table row - use updateTableRowMarker
                        updateTableRowMarker(patientId, colorCode);
                    } else if (element.id !== 'patientProfileHeader') {
                        // It's a card (cards/folders view) - but not the header
                        const marker = element.querySelector('.patient-color-marker');
                        const markerAdd = element.querySelector('.patient-color-marker-add');
                        
                        if (colorCode) {
                            if (markerAdd) markerAdd.remove();
                            if (!marker) {
                                const markerDiv = document.createElement('div');
                                markerDiv.className = 'patient-color-marker';
                                markerDiv.style.cssText = 'position: absolute; top: 8px; right: 8px; width: 12px; height: 12px; border-radius: 50%; background: ' + colorCode + '; border: 2px solid white; z-index: 5; box-shadow: 0 2px 4px rgba(0,0,0,0.2);';
                                markerDiv.setAttribute('onclick', `event.stopPropagation(); showColorMarkerModal(${patientId}, '${colorCode}')`);
                                markerDiv.setAttribute('title', 'Click to change color marker');
                                element.appendChild(markerDiv);
                            } else {
                                marker.style.background = colorCode;
                                marker.setAttribute('onclick', `event.stopPropagation(); showColorMarkerModal(${patientId}, '${colorCode}')`);
                            }
                            
                            // Update card border and glow styling immediately
                            element.setAttribute('data-has-color-marker', 'true');
                            element.classList.add('patient-card-has-marker');
                            element.style.setProperty('--marker-color', colorCode);
                            element.style.setProperty('--marker-color-rgb', hexToRgb(colorCode));
                            element.style.border = `2px solid ${colorCode}`;
                        }
                    }
                } else {
                    // Element not found, try to update table row anyway
                    updateTableRowMarker(patientId, colorCode);
                }
                
                showNotification('Color marker updated', 'success');
            }
        })
        .catch(error => {
            console.error('Error updating color marker:', error);
            showNotification('Failed to update color marker', 'error');
        });
    }
}

// Fetch tags for patients (BATCH API - reduces N calls to 1)
async function fetchTagsForPatients(patients) {
    if (!patients || patients.length === 0) return;

    try {
        const patientIds = patients.map(p => p.id);

        const response = await fetch('/api/patients/tags/batch', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ patient_ids: patientIds })
        });

        const data = await response.json();

        if (data.ok && data.tags) {
            // Update all patients from batch response
            patients.forEach(patient => {
                const tags = data.tags[patient.id] || [];
                patient.tags = tags;

                // Update DOM
                const tagsContainer = document.getElementById(`patientTags_${patient.id}`);
                if (tagsContainer) {
                    let tagsHtml = '';
                    if (tags.length > 0) {
                        tagsHtml = tags.map(tag => `
                            <span class="badge patient-tag"
                                  style="background: ${tag.color || '#6366f1'}; margin-right: 4px; margin-bottom: 4px; cursor: pointer;"
                                  onclick="event.stopPropagation(); removeTagFromPatient(${patient.id}, ${tag.id})"
                                  title="Click to remove tag">
                                ${tag.icon ? `<i class="bi ${tag.icon} me-1"></i>` : ''}
                                ${escapeHtml(tag.name)}
                            </span>
                        `).join('');
                    }
                    tagsHtml += `
                        <button class="btn btn-sm btn-link p-0 add-tag-btn"
                                style="font-size: 0.75rem; color: var(--text);"
                                onclick="event.stopPropagation(); showTagManagementModal(${patient.id})"
                                title="Add tag">
                            <i class="bi bi-plus-circle me-1"></i>Add Tag
                        </button>
                    `;
                    tagsContainer.innerHTML = tagsHtml;
                }
            });
        }
    } catch (error) {
        console.error('Error fetching tags batch:', error);
    }
}

// Show tag management modal
async function showTagManagementModal(patientId) {
    // Fetch available tags
    const tagsResponse = await fetch('/api/patient-tags', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    });
    const tagsData = await tagsResponse.json();
    
    // Fetch patient's current tags
    const patientTagsResponse = await fetch(`/api/patients/${patientId}/tags`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    });
    const patientTagsData = await patientTagsResponse.json();
    
    const availableTags = tagsData.ok ? tagsData.tags : [];
    const patientTags = patientTagsData.ok ? patientTagsData.tags : [];
    const patientTagIds = patientTags.map(t => t.id);
    
    // Create modal HTML if it doesn't exist
    let modal = document.getElementById('tagManagementModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'tagManagementModal';
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-tags me-2"></i>
                            Manage Tags
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Select tags to assign to this patient</p>
                        <div class="tags-list" id="tagsList">
                            <!-- Tags will be added here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    // Build tags list
    const tagsList = modal.querySelector('#tagsList');
    tagsList.innerHTML = '';
    
    if (availableTags.length === 0) {
        tagsList.innerHTML = '<p class="text-muted text-center">No tags available. Create tags first.</p>';
    } else {
        availableTags.forEach(tag => {
            const isAssigned = patientTagIds.includes(tag.id);
            const tagItem = document.createElement('div');

            tagItem.className = 'tag-item mb-2';
            tagItem.style.cssText = 'display: flex; align-items: center; padding: 8px; border: 1px solid var(--border); border-radius: 6px; cursor: pointer; transition: background-color 0.2s ease;';
            if (isAssigned) {
                tagItem.style.background = 'rgba(var(--accent-rgb), 0.1)';
            }
            
            const checkboxId = `tagCheckbox_${patientId}_${tag.id}`;
            tagItem.innerHTML = `
                <input type="checkbox" 
                       id="${checkboxId}"
                       class="form-check-input me-2" 
                       ${isAssigned ? 'checked' : ''}
                       onclick="event.stopPropagation(); togglePatientTag(${patientId}, ${tag.id}, this.checked)">
                <span class="badge" style="background: ${tag.color || '#6366f1'};">
                    ${tag.icon ? `<i class="bi ${tag.icon} me-1"></i>` : ''}
                    ${escapeHtml(tag.name)}
                </span>
            `;
            
            // Make the whole item clickable
            tagItem.addEventListener('click', function(e) {
                // Don't trigger if clicking directly on the checkbox (it handles itself)
                if (e.target.type !== 'checkbox') {
                    const checkbox = this.querySelector(`#${checkboxId}`);
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        togglePatientTag(patientId, tag.id, checkbox.checked);
                    }
                }
            });
            
            // Add hover effect
            tagItem.addEventListener('mouseenter', function() {
                this.style.backgroundColor = isAssigned 
                    ? 'rgba(var(--accent-rgb), 0.15)' 
                    : 'rgba(var(--accent-rgb), 0.05)';
            });
            
            tagItem.addEventListener('mouseleave', function() {
                this.style.backgroundColor = isAssigned 
                    ? 'rgba(var(--accent-rgb), 0.1)' 
                    : 'transparent';
            });
            
            tagsList.appendChild(tagItem);
        });
    }
    
    // Show modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

// Toggle patient tag
function togglePatientTag(patientId, tagId, assign) {
    if (assign) {
        // Assign tag
        fetch(`/api/patients/${patientId}/tags/${tagId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Reload patient tags
                fetch(`/api/patients/${patientId}/tags`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(tagsData => {
                    if (tagsData.ok) {
                        // Update patient in memory
                        const patient = currentFolderPatients.find(p => p.id === patientId);
                        if (patient) {
                            patient.tags = tagsData.tags;
                        }
                        // Also update in paginationState
                        const paginationPatient = paginationState.filteredPatients.find(p => p.id === patientId);
                        if (paginationPatient) {
                            paginationPatient.tags = tagsData.tags;
                        }

                        // Update ALL views (cards/folders and table)
                        updatePatientTagsInDOM(patientId, tagsData.tags);
                        showNotification('Tag added', 'success');
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error assigning tag:', error);
            showNotification('Failed to assign tag', 'error');
        });
    } else {
        // Remove tag
        removeTagFromPatient(patientId, tagId);
    }
}

// Helper function to update tags in ALL views (table + cards/folders)
function updatePatientTagsInDOM(patientId, tags) {
    // Update cards/folders view container
    const cardsContainer = document.getElementById(`patientTags_${patientId}`);
    if (cardsContainer) {
        let tagsHtml = '';
        if (tags && tags.length > 0) {
            tagsHtml = tags.map(tag => `
                <span class="badge patient-tag"
                      style="background: ${tag.color || '#6366f1'}; margin-right: 4px; margin-bottom: 4px; cursor: pointer;"
                      onclick="event.stopPropagation(); removeTagFromPatient(${patientId}, ${tag.id})"
                      title="Click to remove tag">
                    ${tag.icon ? `<i class="bi ${tag.icon} me-1"></i>` : ''}
                    ${escapeHtml(tag.name)}
                </span>
            `).join('');
        }
        tagsHtml += `
            <button class="btn btn-sm btn-link p-0 add-tag-btn"
                    style="font-size: 0.75rem; color: var(--muted);"
                    onclick="event.stopPropagation(); showTagManagementModal(${patientId})"
                    title="Add tag">
                <i class="bi bi-plus-circle me-1"></i>Add Tag
            </button>
        `;
        cardsContainer.innerHTML = tagsHtml;
    }

    // Update table view container
    const tableContainer = document.getElementById(`patientTagsTable_${patientId}`);
    if (tableContainer) {
        let tagsHtml = '';
        if (tags && tags.length > 0) {
            tagsHtml = tags.map(tag => `
                <span class="badge patient-tag"
                      style="background: ${tag.color || '#6366f1'}; font-size: 0.7rem; padding: 0.25rem 0.5rem; cursor: pointer;"
                      onclick="event.stopPropagation(); removeTagFromPatient(${patientId}, ${tag.id})"
                      title="Click to remove tag">
                    ${tag.icon ? `<i class="bi ${tag.icon} me-1"></i>` : ''}
                    ${escapeHtml(tag.name)}
                </span>
            `).join('');
        }
        tagsHtml += `
            <button class="btn btn-sm btn-link p-0 add-tag-btn-table"
                    style="font-size: 0.7rem; color: var(--text); padding: 0;"
                    onclick="event.stopPropagation(); showTagManagementModal(${patientId})"
                    title="Add tag">
                <i class="bi bi-plus-circle me-1"></i>Add
            </button>
        `;
        tableContainer.innerHTML = tagsHtml;
    }
}

// Remove tag from patient
function removeTagFromPatient(patientId, tagId) {
    fetch(`/api/patients/${patientId}/tags/${tagId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Fetch updated tags from API
            fetch(`/api/patients/${patientId}/tags`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(tagsData => {
                if (tagsData.ok) {
                    // Update patient in memory (folder view)
                    const patient = currentFolderPatients.find(p => p.id === patientId);
                    if (patient) {
                        patient.tags = tagsData.tags;
                    }
                    
                    // Update patient in paginationState (table/cards view)
                    const paginationPatient = paginationState.filteredPatients.find(p => p.id === patientId);
                    if (paginationPatient) {
                        paginationPatient.tags = tagsData.tags;
                    }
                    
                    // Update ALL views (table + cards/folders) using helper function
                    updatePatientTagsInDOM(patientId, tagsData.tags);
                    
                    // Also update patient profile header if on patient page
                    if (typeof loadPatientTags === 'function') {
                        loadPatientTags(patientId);
                    }
                    
                    showNotification('Tag removed', 'success');
                }
            })
            .catch(error => {
                console.error('Error fetching updated tags:', error);
                showNotification('Tag removed but failed to refresh', 'warning');
            });
        }
    })
    .catch(error => {
        console.error('Error removing tag:', error);
        showNotification('Failed to remove tag', 'error');
    });
}

// ============================================
// OLD FilterManager Class REMOVED - Using UnifiedFilterManager instead

// ============================================
// Folder Management Functions
// ============================================

// Show create folder modal
function showCreateFolderModal() {
    const modal = new bootstrap.Modal(document.getElementById('createFolderModal'));
    document.getElementById('folderName').value = '';
    document.getElementById('createFolderMessage').classList.add('d-none');
    modal.show();
}

// Show change folder icon modal
function showChangeFolderIconModal(folderId, currentIcon, currentGradient) {
    const modal = new bootstrap.Modal(document.getElementById('changeFolderIconModal'));
    
    // Check if it's a system folder and get preferences from localStorage
    const isSystemFolder = folderId.toString().startsWith('system_');
    let actualIcon = currentIcon;
    let actualGradient = currentGradient;
    
    if (isSystemFolder) {
        const systemFolderPrefs = JSON.parse(localStorage.getItem('systemFolderPreferences') || '{}');
        if (systemFolderPrefs[folderId]) {
            actualIcon = systemFolderPrefs[folderId].icon || currentIcon || 'bi-folder-fill';
            actualGradient = systemFolderPrefs[folderId].gradient_color || currentGradient || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        } else {
            actualIcon = currentIcon || 'bi-folder-fill';
            actualGradient = currentGradient || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        }
    } else {
        actualIcon = currentIcon || 'bi-folder';
        actualGradient = currentGradient || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
    }
    
    document.getElementById('changeFolderIconId').value = folderId;
    document.getElementById('selectedIcon').value = actualIcon;
    document.getElementById('selectedGradient').value = actualGradient;
    document.getElementById('customGradient').value = '';
    document.getElementById('changeFolderIconMessage').classList.add('d-none');
    
    // Populate Bootstrap Icons
    const iconGrid = document.getElementById('iconSelectionGrid');
    const folderIcons = [
        'bi-folder', 'bi-folder-fill', 'bi-folder2', 'bi-folder2-open',
        'bi-folder-symlink', 'bi-folder-symlink-fill', 'bi-folder-check',
        'bi-folder-x', 'bi-folder-plus', 'bi-folder-minus',
        'bi-archive', 'bi-archive-fill', 'bi-briefcase', 'bi-briefcase-fill',
        'bi-collection', 'bi-collection-fill', 'bi-inbox', 'bi-inbox-fill'
    ];
    
    iconGrid.innerHTML = '';
    folderIcons.forEach(iconClass => {
        const isSelected = iconClass === currentIcon;
        iconGrid.innerHTML += `
            <div class="col-3 col-md-2">
                <div class="icon-option ${isSelected ? 'selected' : ''}" 
                     data-icon="${iconClass}"
                     onclick="selectIcon('${iconClass}')"
                     style="padding: 1rem; text-align: center; border: 2px solid ${isSelected ? 'var(--accent)' : 'var(--border)'}; border-radius: 8px; cursor: pointer; background: ${isSelected ? 'var(--accent)' : 'var(--bg)'}; transition: all 0.2s ease;">
                    <i class="bi ${iconClass}" style="font-size: 2rem; color: ${isSelected ? 'white' : 'var(--text)'};"></i>
                </div>
            </div>
        `;
    });
    
    // Populate Gradient Presets
    const gradientGrid = document.getElementById('gradientSelectionGrid');
    const gradients = [
        { name: 'Purple', value: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' },
        { name: 'Pink', value: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' },
        { name: 'Blue', value: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)' },
        { name: 'Green', value: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)' },
        { name: 'Orange', value: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)' },
        { name: 'Teal', value: 'linear-gradient(135deg, #30cfd0 0%, #330867 100%)' },
        { name: 'Red', value: 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)' },
        { name: 'Dark', value: 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)' }
    ];
    
    gradientGrid.innerHTML = '';
    gradients.forEach((grad, index) => {
        // Compare gradients more flexibly (handle whitespace differences)
        const isSelected = grad.value.trim() === actualGradient.trim();
        gradientGrid.innerHTML += `
            <div class="col-6 col-md-3">
                <div class="gradient-option ${isSelected ? 'selected' : ''}" 
                     data-gradient="${grad.value}"
                     onclick="selectGradient('${grad.value}')"
                     style="height: 60px; border: 2px solid ${isSelected ? 'var(--accent)' : 'var(--border)'}; border-radius: 8px; cursor: pointer; background: ${grad.value}; position: relative; overflow: hidden;">
                    <div style="position: absolute; bottom: 4px; left: 4px; right: 4px; background: rgba(0,0,0,0.6); color: white; padding: 2px 4px; border-radius: 4px; font-size: 0.7rem; text-align: center;">
                        ${grad.name}
                    </div>
                    ${isSelected ? '<i class="bi bi-check-circle-fill" style="position: absolute; top: 4px; right: 4px; color: white; font-size: 1.2rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"></i>' : ''}
                </div>
            </div>
        `;
    });
    
    modal.show();
}

// Select icon
function selectIcon(iconClass) {
    document.getElementById('selectedIcon').value = iconClass;
    document.querySelectorAll('.icon-option').forEach(el => {
        el.classList.remove('selected');
        el.style.borderColor = 'var(--border)';
        el.style.background = 'var(--bg)';
        el.querySelector('i').style.color = 'var(--text)';
    });
    const selected = document.querySelector(`[data-icon="${iconClass}"]`);
    if (selected) {
        selected.classList.add('selected');
        selected.style.borderColor = 'var(--accent)';
        selected.style.background = 'var(--accent)';
        selected.querySelector('i').style.color = 'white';
    }
}

// Select gradient
function selectGradient(gradientValue) {
    document.getElementById('selectedGradient').value = gradientValue;
    document.getElementById('customGradient').value = '';
    document.querySelectorAll('.gradient-option').forEach(el => {
        el.classList.remove('selected');
        el.style.borderColor = 'var(--border)';
        el.querySelector('.bi-check-circle-fill')?.remove();
    });
    const selected = document.querySelector(`[data-gradient="${gradientValue}"]`);
    if (selected) {
        selected.classList.add('selected');
        selected.style.borderColor = 'var(--accent)';
        selected.innerHTML += '<i class="bi bi-check-circle-fill" style="position: absolute; top: 4px; right: 4px; color: white; font-size: 1.2rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"></i>';
    }
}

// Create folder
document.getElementById('createFolderForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('folderName').value.trim();
    const messageEl = document.getElementById('createFolderMessage');
    
    if (!name) {
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'Folder name is required';
        messageEl.classList.remove('d-none');
        return;
    }
    
    fetch('/api/patient-folders', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('createFolderModal'));
            modal.hide();
            loadFolders();
        } else {
            messageEl.className = 'alert alert-danger';
            messageEl.textContent = data.error || 'Failed to create folder';
            messageEl.classList.remove('d-none');
        }
    })
    .catch(error => {
        console.error('Error creating folder:', error);
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'An error occurred while creating the folder';
        messageEl.classList.remove('d-none');
    });
});

// Create sub-folder
document.getElementById('createSubFolderForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('subFolderName').value.trim();
    const parentId = document.getElementById('subFolderParentId').value;
    const parentType = document.getElementById('subFolderParentType').value;
    const messageEl = document.getElementById('createSubFolderMessage');
    
    if (!name) {
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'Sub-folder name is required';
        messageEl.classList.remove('d-none');
        return;
    }
    
    fetch('/api/patient-folders', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            name,
            parent_id: parentId,
            parent_type: parentType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('createSubFolderModal'));
            modal.hide();
            showNotification('Sub-folder created successfully', 'success');
            // Reload sub-folders if we're in a folder view
            if (currentFolderId) {
                loadSubFolders(currentFolderId, currentFolderType);
            } else {
                loadFolders();
            }
        } else {
            messageEl.className = 'alert alert-danger';
            messageEl.textContent = data.error || 'Failed to create sub-folder';
            messageEl.classList.remove('d-none');
        }
    })
    .catch(error => {
        console.error('Error creating sub-folder:', error);
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'An error occurred while creating the sub-folder';
        messageEl.classList.remove('d-none');
    });
});

// Show rename folder modal
function showRenameFolderModal(folderId, currentName) {
    const modal = new bootstrap.Modal(document.getElementById('renameFolderModal'));
    document.getElementById('renameFolderId').value = folderId;
    document.getElementById('renameFolderName').value = currentName;
    document.getElementById('renameFolderMessage').classList.add('d-none');
    modal.show();
}

// Rename folder
document.getElementById('renameFolderForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const folderId = document.getElementById('renameFolderId').value;
    const name = document.getElementById('renameFolderName').value.trim();
    const messageEl = document.getElementById('renameFolderMessage');
    
    if (!name) {
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'Folder name is required';
        messageEl.classList.remove('d-none');
        return;
    }
    
    fetch(`/api/patient-folders/${folderId}`, {
        method: 'PUT',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('renameFolderModal'));
            modal.hide();
            showNotification('Folder renamed successfully', 'success');
            // If we're in a folder view, reload sub-folders
            if (currentFolderId) {
                const isSystem = currentFolderId.toString().startsWith('system_');
                const parentType = isSystem ? 'system' : 'custom';
                loadSubFolders(currentFolderId, parentType);
            } else {
                loadFolders();
            }
        } else {
            messageEl.className = 'alert alert-danger';
            messageEl.textContent = data.error || 'Failed to rename folder';
            messageEl.classList.remove('d-none');
        }
    })
    .catch(error => {
        console.error('Error renaming folder:', error);
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'An error occurred while renaming the folder';
        messageEl.classList.remove('d-none');
    });
});

// Delete folder
function deleteFolder(folderId) {
    showConfirmModal(
        'Delete Folder',
        'Are you sure you want to delete this folder? Patients will not be deleted, only removed from the folder.',
        function() {
            performDeleteFolder(folderId);
        },
        'Delete'
    );
}

function performDeleteFolder(folderId) {
    
    fetch(`/api/patient-folders/${folderId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            showNotification('Folder deleted successfully', 'success');
            // If we're in a folder view, reload sub-folders
            if (currentFolderId) {
                const isSystem = currentFolderId.toString().startsWith('system_');
                const parentType = isSystem ? 'system' : 'custom';
                loadSubFolders(currentFolderId, parentType);
            } else {
                loadFolders();
            }
        } else {
            showNotification(data.error || 'Failed to delete folder', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting folder:', error);
        showNotification('An error occurred while deleting the folder', 'error');
    });
}

// Load all folders recursively (including sub-folders) for modals
async function loadAllFoldersForModal() {
    const allFolders = [];
    
    // Add system folders
    for (const systemFolder of systemFoldersData) {
        allFolders.push({
            id: systemFolder.id,
            name: systemFolder.name,
            type: 'system',
            level: 0
        });
        
        // Load sub-folders for system folder
        try {
            const response = await fetch(`/api/patient-folders/${systemFolder.id}/sub-folders/system`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            if (data.ok && data.sub_folders) {
                data.sub_folders.forEach(subFolder => {
                    allFolders.push({
                        id: subFolder.id,
                        name: subFolder.name,
                        type: 'custom',
                        level: 1,
                        parentName: systemFolder.name
                    });
                });
            }
        } catch (error) {
            console.error('Error loading system sub-folders:', error);
        }
    }
    
    // Add custom folders
    for (const customFolder of customFoldersData) {
        allFolders.push({
            id: customFolder.id,
            name: customFolder.name,
            type: 'custom',
            level: 0
        });
        
        // Load sub-folders for custom folder
        try {
            const response = await fetch(`/api/patient-folders/${customFolder.id}/sub-folders/custom`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            if (data.ok && data.sub_folders) {
                data.sub_folders.forEach(subFolder => {
                    allFolders.push({
                        id: subFolder.id,
                        name: subFolder.name,
                        type: 'custom',
                        level: 1,
                        parentName: customFolder.name
                    });
                });
            }
        } catch (error) {
            console.error('Error loading custom sub-folders:', error);
        }
    }
    
    return allFolders;
}

// Show move patient modal
async function showMovePatientModal(patientId) {
    const modal = new bootstrap.Modal(document.getElementById('movePatientModal'));
    document.getElementById('movePatientId').value = patientId;
    document.getElementById('movePatientMessage').classList.add('d-none');
    
    // Set modal title
    const modalTitle = document.querySelector('#movePatientModal .modal-title');
    if (modalTitle) {
        modalTitle.innerHTML = '<i class="bi bi-folder me-2"></i>Move Patient to Folder';
    }
    
    // Change button text
    const buttonText = document.getElementById('movePatientButtonText');
    if (buttonText) {
        buttonText.textContent = 'Move Patient';
    }
    
    // Populate folder select
    const select = document.getElementById('movePatientFolderSelect');
    select.innerHTML = '<option value="">-- Select Folder --</option>';
    
    // Load all folders including sub-folders
    const allFolders = await loadAllFoldersForModal();
    
    allFolders.forEach(folder => {
        const option = document.createElement('option');
        option.value = folder.id;
        const displayName = folder.level === 1 
            ? `${folder.parentName} > ${folder.name}`
            : folder.name;
        option.textContent = displayName;
        select.appendChild(option);
    });
    
    modal.show();
}

// Confirm move/add patient
function confirmMovePatient() {
    const patientId = document.getElementById('movePatientId').value;
    const folderId = document.getElementById('movePatientFolderSelect').value;
    const messageEl = document.getElementById('movePatientMessage');
    
    if (!folderId) {
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'Please select a folder';
        messageEl.classList.remove('d-none');
        return;
    }
    
    // Check if this is a bulk move operation
    const isBulkMove = window.bulkMovePatientIds && window.bulkMovePatientIds.length > 0;
    const patientsToMove = isBulkMove ? window.bulkMovePatientIds : [parseInt(patientId)];
    
    // Check if this is a "move" operation (from modal title)
    const modalTitle = document.querySelector('#movePatientModal .modal-title');
    const isMove = modalTitle && modalTitle.textContent.includes('Move');
    
    // Move all patients
    let completed = 0;
    let failed = 0;
    
    patientsToMove.forEach(pid => {
        fetch(`/api/patient-folders/${folderId}/patients`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                patient_id: parseInt(pid),
                move: isMove // true for move, false for add
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                completed++;
            } else {
                failed++;
            }
            
            // When all requests complete
            if (completed + failed === patientsToMove.length) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('movePatientModal'));
                modal.hide();
                
                // Reset modal title and button text
                const modalTitle = document.querySelector('#movePatientModal .modal-title');
                if (modalTitle) {
                    modalTitle.innerHTML = '<i class="bi bi-folder me-2"></i>Move Patient to Folder';
                }
                const buttonText = document.getElementById('movePatientButtonText');
                if (buttonText) {
                    buttonText.textContent = 'Move Patient';
                }
                
                // Clear bulk move IDs
                if (isBulkMove) {
                    window.bulkMovePatientIds = [];
                    selectedPatients = [];
                    updateSelectionUI();
                }
                
                // Refresh view via API based on operation type and current view
                if (isMove) {
                    // Move operation: open the destination folder to show where patients went
                    openFolder(folderId);
                    // Also refresh patients data for cards/table views
                    refreshPatientsData();
                    showNotification(`${completed} patient(s) moved successfully${failed > 0 ? `, ${failed} failed` : ''}`, completed > 0 ? 'success' : 'error');
                } else {
                    // Add operation: refresh current folder if open, or refresh folders view
                    if (currentFolderId) {
                        openFolder(currentFolderId);
                        // Update patient count locally
                        updateFolderPatientCount(currentFolderId, completed);
                    } else {
                        // Refresh current view based on view mode
                        if (currentViewMode === 'folders') {
                            loadFolders();
                        } else {
                            // Refresh cards/table view
                            refreshPatientsData();
                        }
                    }
                    showNotification(`${completed} patient(s) added successfully${failed > 0 ? `, ${failed} failed` : ''}`, completed > 0 ? 'success' : 'error');
                }
            }
        })
        .catch(error => {
            failed++;
            if (completed + failed === patientsToMove.length) {
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = `Error: ${completed} succeeded, ${failed} failed`;
                messageEl.classList.remove('d-none');
            }
        });
    });
    
    // Error handling is done inside the forEach loop above
    // No need for additional catch block here
}

// Change folder icon and color
document.getElementById('changeFolderIconForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const folderId = document.getElementById('changeFolderIconId').value;
    const icon = document.getElementById('selectedIcon').value;
    const gradientColor = document.getElementById('selectedGradient').value || document.getElementById('customGradient').value;
    const messageEl = document.getElementById('changeFolderIconMessage');
    
    if (!icon || !gradientColor) {
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'Please select an icon and gradient color';
        messageEl.classList.remove('d-none');
        return;
    }
    
    // Check if it's a system folder (starts with 'system_')
    const isSystemFolder = folderId.toString().startsWith('system_');
    
    if (isSystemFolder) {
        // Store system folder customization in localStorage
        const systemFolderPrefs = JSON.parse(localStorage.getItem('systemFolderPreferences') || '{}');
        systemFolderPrefs[folderId] = {
            icon: icon,
            gradient_color: gradientColor
        };
        localStorage.setItem('systemFolderPreferences', JSON.stringify(systemFolderPrefs));
        
        // Update the folder in foldersData immediately
        const folder = foldersData.find(f => f.id === folderId);
        if (folder) {
            folder.icon = icon;
            folder.gradient_color = gradientColor;
        }
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('changeFolderIconModal'));
        modal.hide();
        
        // Force re-render to apply changes
        renderFoldersView();
        showNotification('Folder icon and color updated successfully', 'success');
    } else {
        // Update custom folder via API
        const actualFolderId = parseInt(folderId);
        
        fetch(`/api/patient-folders/${actualFolderId}`, {
            method: 'PUT',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ icon, gradient_color: gradientColor })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('changeFolderIconModal'));
                modal.hide();
                showNotification('Folder icon and color updated successfully', 'success');
                // If we're in a folder view, reload sub-folders
                if (currentFolderId) {
                    const isSystem = currentFolderId.toString().startsWith('system_');
                    const parentType = isSystem ? 'system' : 'custom';
                    loadSubFolders(currentFolderId, parentType);
                } else {
                    loadFolders();
                }
            } else {
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = data.error || 'Failed to update folder';
                messageEl.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Error updating folder icon:', error);
            messageEl.className = 'alert alert-danger';
            messageEl.textContent = 'An error occurred while updating the folder';
            messageEl.classList.remove('d-none');
        });
    }
});

// Remove patient from folder
function removePatientFromFolder(patientId) {
    if (!currentFolderId) return;
    
    showConfirmModal(
        'Remove Patient',
        'Remove this patient from the folder?',
        function() {
            performRemovePatientFromFolder(patientId);
        },
        'Remove'
    );
}

function performRemovePatientFromFolder(patientId) {
    if (!currentFolderId) return;
    
    fetch(`/api/patient-folders/${currentFolderId}/patients/${patientId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Update patient count locally before refreshing
            updateFolderPatientCount(currentFolderId, -1);
            openFolder(currentFolderId);
            // Also refresh patients data for cards/table views
            refreshPatientsData();
            showNotification('Patient removed from folder successfully', 'success');
        } else {
            showNotification(data.error || 'Failed to remove patient', 'error');
        }
    })
    .catch(error => {
        console.error('Error removing patient:', error);
        showNotification('An error occurred while removing the patient', 'error');
    });
}

// Show add to folder modal
async function showAddToFolderModal(patientId) {
    const modal = new bootstrap.Modal(document.getElementById('movePatientModal'));
    document.getElementById('movePatientId').value = patientId;
    document.getElementById('movePatientMessage').classList.add('d-none');
    
    // Change modal title
    const modalTitle = document.querySelector('#movePatientModal .modal-title');
    if (modalTitle) {
        modalTitle.innerHTML = '<i class="bi bi-folder-plus me-2"></i>Add Patient to Folder';
    }
    
    // Change button text
    const buttonText = document.getElementById('movePatientButtonText');
    if (buttonText) {
        buttonText.textContent = 'Add to Folder';
    }
    
    // Populate folder select
    const select = document.getElementById('movePatientFolderSelect');
    select.innerHTML = '<option value="">-- Select Folder --</option>';
    
    // Load all folders including sub-folders
    const allFolders = await loadAllFoldersForModal();
    
    allFolders.forEach(folder => {
        const option = document.createElement('option');
        option.value = folder.id;
        const displayName = folder.level === 1 
            ? `${folder.parentName} > ${folder.name}`
            : folder.name;
        option.textContent = displayName;
        select.appendChild(option);
    });
    
    modal.show();
}