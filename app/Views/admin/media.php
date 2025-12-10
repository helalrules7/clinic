<?php
/**
 * Admin Media Management Template - Redesigned
 */
?>

<link href="/app/Views/secretary/assets/css/details.css?v=<?= file_exists(__DIR__ . '/../secretary/assets/css/details.css') ? filemtime(__DIR__ . '/../secretary/assets/css/details.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/secretary/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/../secretary/assets/css/dashboard.css') ? filemtime(__DIR__ . '/../secretary/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/dashboard.css') ? filemtime(__DIR__ . '/../doctor/assets/css/dashboard.css') : time() ?>" rel="stylesheet">

<style>
/* Media Page Styles - RTL Support */
:root {
    --media-bg: var(--bg);
    --media-card: var(--card);
    --media-border: var(--border);
    --media-text: var(--text);
    --media-accent: var(--accent);
    --media-shadow: var(--shadow);
    --accent-rgb: 14, 165, 233;
}

.dark {
    --media-bg: var(--bg);
    --media-card: var(--card);
    --media-border: var(--border);
    --media-text: var(--text);
    --media-accent: var(--accent);
    --media-shadow: rgba(0, 0, 0, 0.4);
    --accent-rgb: 56, 189, 248;
}

/* LTR Support */
.media-header,
.media-toolbar,
.media-container {
    direction: ltr;
    text-align: left;
}

/* Header Section */
.media-header {
    background: var(--media-card);
    border: 1px solid var(--media-border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px var(--media-shadow);
}

.media-header h4 {
    color: var(--media-text);
    font-weight: 600;
    margin: 0;
}

/* Statistics Cards */
.media-stats-wrapper {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.media-stat-card {
    background: var(--media-card);
    border: 1px solid var(--media-border);
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
    box-shadow: 0 2px 8px var(--media-shadow);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.media-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px var(--media-shadow);
}

.media-stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--media-accent);
    margin-bottom: 0.5rem;
}

.media-stat-label {
    font-size: 0.875rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Toolbar */
.media-toolbar {
    background: var(--media-card);
    border: 1px solid var(--media-border);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px var(--media-shadow);
}

.media-toolbar-left {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.media-toolbar-right {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

/* View Mode Buttons */
.view-mode-group {
    display: flex;
    gap: 0.25rem;
    background: var(--bg-alt);
    padding: 0.25rem;
    border-radius: 8px;
    border: 1px solid var(--media-border);
}

.view-mode-btn {
    padding: 0.5rem 0.75rem;
    border: none;
    background: transparent;
    color: var(--muted);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1rem;
}

.view-mode-btn:hover {
    background: var(--media-card);
    color: var(--media-text);
}

.view-mode-btn.active {
    background: var(--media-accent);
    color: white;
}

/* Sort and Filter Controls */
.media-controls {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
}

.media-controls select,
.media-controls input {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--media-border);
    border-radius: 8px;
    background: var(--media-card);
    color: var(--media-text);
    font-size: 0.875rem;
}

.media-controls select:focus,
.media-controls input:focus {
    outline: none;
    border-color: var(--media-accent);
    box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.1);
}

/* Bulk Actions */
.bulk-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    padding: 0.5rem 1rem;
    background: var(--bg-alt);
    border-radius: 8px;
    border: 1px solid var(--media-border);
}

.bulk-actions .badge {
    background: var(--media-accent);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-weight: 600;
}

/* Media Container */
.media-container {
    background: var(--media-card);
    border: 1px solid var(--media-border);
    border-radius: 12px;
    padding: 1.5rem;
    min-height: 400px;
    box-shadow: 0 2px 8px var(--media-shadow);
}

/* Thumbnail View */
.media-thumbnail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.media-thumbnail-grid.view-small {
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.75rem;
}

.media-thumbnail-grid.view-large {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.25rem;
}

.media-thumbnail-item {
    position: relative;
    background: var(--bg-alt);
    border: 2px solid var(--media-border);
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    aspect-ratio: 1;
}

.media-thumbnail-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px var(--media-shadow);
    border-color: var(--media-accent);
}

.media-thumbnail-item.selected {
    border-color: var(--media-accent);
    box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.3);
}

.media-thumbnail-item.selected::after {
    content: '';
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 24px;
    height: 24px;
    background: var(--media-accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.media-thumbnail-item.selected::before {
    content: '✓';
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 24px;
    height: 24px;
    color: white;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 11;
    font-size: 0.875rem;
}

.media-thumbnail-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    background: var(--bg);
}

.media-thumbnail-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
    padding: 0.75rem;
    color: white;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.media-thumbnail-item:hover .media-thumbnail-overlay {
    opacity: 1;
}

.media-thumbnail-name {
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin: 0;
}

.media-thumbnail-size {
    font-size: 0.65rem;
    opacity: 0.9;
    margin-top: 0.25rem;
}

/* Table View */
.media-table-view {
    width: 100%;
    border-collapse: collapse;
}

.media-table-view thead th {
    background: var(--bg-alt);
    padding: 1rem;
    text-align: right;
    font-weight: 600;
    color: var(--media-text);
    border-bottom: 2px solid var(--media-border);
    cursor: pointer;
    user-select: none;
    transition: background 0.2s ease;
}

.media-table-view thead th:hover {
    background: var(--media-card);
}

.media-table-view thead th.sortable::after {
    content: ' ↕';
    opacity: 0.5;
    margin-right: 0.5rem;
}

.media-table-view thead th.sort-asc::after {
    content: ' ↑';
    opacity: 1;
    color: var(--media-accent);
}

.media-table-view thead th.sort-desc::after {
    content: ' ↓';
    opacity: 1;
    color: var(--media-accent);
}

.media-table-view tbody tr {
    border-bottom: 1px solid var(--media-border);
    transition: background 0.2s ease;
}

.media-table-view tbody tr:hover {
    background: var(--bg-alt);
}

.media-table-view tbody td {
    padding: 1rem;
    color: var(--media-text);
    vertical-align: middle;
}

.media-table-preview {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    background: var(--bg);
}

.media-table-name {
    font-weight: 600;
    color: var(--media-text);
}

.media-table-path {
    font-size: 0.875rem;
    color: var(--muted);
    font-family: monospace;
}

/* Folder View */
.media-folder-view {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.media-folder-item {
    background: var(--media-card);
    border: 1px solid var(--media-border);
    border-radius: 12px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.media-folder-item:hover {
    background: var(--bg-alt);
    border-color: var(--media-accent);
    transform: translateX(-4px);
}

.media-folder-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.media-folder-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--media-accent), var(--success));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.media-folder-name {
    font-weight: 600;
    color: var(--media-text);
    font-size: 1.1rem;
}

.media-folder-count {
    margin-right: auto;
    color: var(--muted);
    font-size: 0.875rem;
}

.media-folder-content {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 0.75rem;
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--media-border);
}

.media-folder-content.collapsed {
    display: none;
}

/* Context Menu - Blur Glass Effect */
.context-menu {
    position: fixed;
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 12px;
    padding: 0.5rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
    z-index: 10000;
    min-width: 200px;
    opacity: 0;
    transform: scale(0.9) translateY(-5px);
    transition: opacity 0.15s ease, transform 0.15s ease;
    pointer-events: none;
    top: 0;
    left: 0;
}

.context-menu.show {
    opacity: 1;
    transform: scale(1) translateY(0);
    pointer-events: all;
}

.dark .context-menu {
    background: rgba(30, 41, 59, 0.85);
    border-color: rgba(255, 255, 255, 0.15);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.05) inset;
}

.context-menu-item {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--media-text);
    transition: all 0.15s ease;
    font-size: 0.875rem;
    position: relative;
    margin: 0.125rem 0;
}

.context-menu-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--media-accent);
    border-radius: 0 3px 3px 0;
    opacity: 0;
    transition: opacity 0.15s ease;
}

.context-menu-item:hover {
    background: rgba(var(--accent-rgb), 0.15);
    color: var(--media-accent);
    transform: translateX(2px);
}

.context-menu-item:hover::before {
    opacity: 1;
}

.context-menu-item.danger:hover {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}

.context-menu-item.danger::before {
    background: #ef4444;
}

.context-menu-item i {
    width: 20px;
    text-align: center;
}

.context-menu-divider {
    height: 1px;
    background: var(--media-border);
    margin: 0.5rem 0;
}

/* File Info Modal */
.file-info-modal .modal-content {
    background: var(--media-card);
    border: 1px solid var(--media-border);
    color: var(--media-text);
}

.file-info-preview {
    width: 100%;
    max-height: 300px;
    object-fit: contain;
    border-radius: 8px;
    background: var(--bg-alt);
    margin-bottom: 1rem;
}

.file-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1rem;
}

.file-info-item {
    padding: 0.75rem;
    background: var(--bg-alt);
    border-radius: 8px;
    border: 1px solid var(--media-border);
}

.file-info-label {
    font-size: 0.75rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.file-info-value {
    font-size: 0.875rem;
    color: var(--media-text);
    font-weight: 600;
    word-break: break-all;
}

/* Empty State */
.media-empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--muted);
}

.media-empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.media-empty-state h5 {
    color: var(--media-text);
    margin-bottom: 0.5rem;
}

/* Loading State */
.media-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    color: var(--muted);
}

.media-loading .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.3rem;
    margin-bottom: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .media-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .media-toolbar-left,
    .media-toolbar-right {
        width: 100%;
        justify-content: space-between;
    }
    
    .media-thumbnail-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
    
    .media-table-view {
        font-size: 0.875rem;
    }
    
    .file-info-grid {
        grid-template-columns: 1fr;
    }
}

/* Selection Checkbox */
.media-checkbox {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 20px;
    height: 20px;
    border: 2px solid white;
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.3);
    cursor: pointer;
    z-index: 5;
    transition: all 0.2s ease;
}

.media-checkbox:hover {
    background: rgba(0, 0, 0, 0.5);
}

.media-checkbox input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
    margin: 0;
}

.media-checkbox input:checked + .checkmark {
    display: block;
}

.checkmark {
    display: none;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 12px;
    height: 12px;
    background: var(--media-accent);
    border-radius: 2px;
}

.checkmark::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 10px;
    font-weight: bold;
}
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="media-header">
        <h4>
            <i class="bi bi-images me-2"></i>
            Media Management
        </h4>
        <p class="text-muted mb-0">Manage and view all media files in the system</p>
    </div>

    <!-- Statistics -->
    <div class="media-stats-wrapper">
        <div class="media-stat-card">
            <div class="media-stat-value" id="totalFiles">0</div>
            <div class="media-stat-label">Total Files</div>
        </div>
        <div class="media-stat-card">
            <div class="media-stat-value" id="totalSize">0 MB</div>
            <div class="media-stat-label">Total Size</div>
        </div>
        <div class="media-stat-card">
            <div class="media-stat-value" id="selectedCount">0</div>
            <div class="media-stat-label">Selected</div>
        </div>
        <div class="media-stat-card">
            <div class="media-stat-value" id="foldersCount">0</div>
            <div class="media-stat-label">Folders</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="media-toolbar">
        <div class="media-toolbar-left">
            <!-- View Mode Toggle -->
            <div class="view-mode-group">
                <button class="view-mode-btn active" data-view="thumbnail" title="Thumbnail View">
                    <i class="bi bi-grid-3x3-gap"></i>
                </button>
                <button class="view-mode-btn" data-view="table" title="Table View">
                    <i class="bi bi-list-ul"></i>
                </button>
                <button class="view-mode-btn" data-view="folder" title="Folder View">
                    <i class="bi bi-folder"></i>
                </button>
            </div>

            <!-- Thumbnail Size Toggle (only for thumbnail view) -->
            <div class="view-mode-group" id="thumbnailSizeGroup" style="display: none;">
                <button class="view-mode-btn active" data-size="medium" title="Medium">
                    <i class="bi bi-square"></i>
                </button>
                <button class="view-mode-btn" data-size="small" title="Small">
                    <i class="bi bi-square-fill" style="font-size: 0.75rem;"></i>
                </button>
                <button class="view-mode-btn" data-size="large" title="Large">
                    <i class="bi bi-square-fill"></i>
                </button>
            </div>

            <!-- Bulk Actions -->
            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="badge" id="selectedBadge">0 selected</span>
                <button class="btn btn-sm btn-danger" onclick="deleteSelected()">
                    <i class="bi bi-trash me-1"></i>Delete Selected
                </button>
                <button class="btn btn-sm btn-secondary" onclick="clearSelection()">
                    <i class="bi bi-x-lg me-1"></i>Clear Selection
                </button>
            </div>
        </div>

        <div class="media-toolbar-right">
            <!-- Sort Control -->
            <div class="media-controls">
                <label class="form-label mb-0 me-2">Sort:</label>
                <select id="sortBy" class="form-select form-select-sm">
                    <option value="name">Name</option>
                    <option value="size">Size</option>
                    <option value="date">Date</option>
                    <option value="type">Type</option>
                </select>
                <select id="sortOrder" class="form-select form-select-sm">
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
                </select>
            </div>

            <!-- Filter Control -->
            <div class="media-controls">
                <label class="form-label mb-0 me-2">Folder:</label>
                <select id="folderFilter" class="form-select form-select-sm">
                    <option value="all">All</option>
                </select>
            </div>

            <!-- Search -->
            <div class="media-controls">
                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search..." style="min-width: 200px;">
            </div>

            <!-- Actions -->
            <button class="btn btn-primary btn-sm" onclick="selectAll()">
                <i class="bi bi-check-square me-1"></i>Select All
            </button>
        </div>
    </div>

    <!-- Media Container -->
    <div class="media-container">
        <!-- Loading State -->
        <div class="media-loading" id="loadingState">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted">Loading media files...</p>
        </div>

        <!-- Thumbnail View -->
        <div id="thumbnailView" style="display: none;">
            <div class="media-thumbnail-grid" id="thumbnailGrid"></div>
        </div>

        <!-- Table View -->
        <div id="tableView" style="display: none;">
            <div class="table-responsive">
                <table class="media-table-view">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="name">
                                <input type="checkbox" id="selectAllTable" onchange="toggleSelectAllTable(this.checked)">
                            </th>
                            <th class="sortable" data-sort="preview">Preview</th>
                            <th class="sortable" data-sort="name">Name</th>
                            <th class="sortable" data-sort="path">Path</th>
                            <th class="sortable" data-sort="size">Size</th>
                            <th class="sortable" data-sort="type">Type</th>
                            <th class="sortable" data-sort="date">Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
        </div>

        <!-- Folder View -->
        <div id="folderView" style="display: none;">
            <div class="media-folder-view" id="folderViewContainer"></div>
        </div>

        <!-- Empty State -->
        <div class="media-empty-state" id="emptyState" style="display: none;">
            <i class="bi bi-folder-x"></i>
            <h5>No Files</h5>
            <p>No media files found</p>
        </div>
    </div>
</div>

<!-- Context Menu -->
<div class="context-menu" id="contextMenu" onclick="event.stopPropagation();">
    <div class="context-menu-item" onclick="event.stopPropagation(); showFileInfo(contextMenuFile); hideContextMenu();">
        <i class="bi bi-info-circle"></i>
        <span>File Info</span>
    </div>
    <div class="context-menu-item" onclick="event.stopPropagation(); downloadFile(contextMenuFile); hideContextMenu();">
        <i class="bi bi-download"></i>
        <span>Download</span>
    </div>
    <div class="context-menu-divider"></div>
    <div class="context-menu-item danger" onclick="event.stopPropagation(); deleteSingleFile(contextMenuFile); hideContextMenu();">
        <i class="bi bi-trash"></i>
        <span>Delete</span>
    </div>
</div>

<!-- File Info Modal -->
<div class="modal fade file-info-modal" id="fileInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>
                    File Information
                </h5>
            </div>
            <div class="modal-body">
                <div id="fileInfoPreview"></div>
                <div class="file-info-grid" id="fileInfoGrid"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="deleteFromInfoModal">
                    <i class="bi bi-trash me-2"></i>Delete File
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global State
let mediaFiles = [];
let filteredFiles = [];
let selectedFiles = new Set();
let currentView = 'thumbnail'; // thumbnail, table, folder
let thumbnailSize = 'medium'; // small, medium, large
let sortBy = 'name';
let sortOrder = 'asc';
let currentFolder = 'all';
let folders = [];
let contextMenuFile = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadMediaFiles();
    setupEventListeners();
    setupContextMenu();
});

// Setup Event Listeners
function setupEventListeners() {
    // View mode toggle
    document.querySelectorAll('.view-mode-btn[data-view]').forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            switchView(view);
        });
    });

    // Thumbnail size toggle
    document.querySelectorAll('.view-mode-btn[data-size]').forEach(btn => {
        btn.addEventListener('click', function() {
            const size = this.dataset.size;
            setThumbnailSize(size);
        });
    });

    // Sort controls
    document.getElementById('sortBy').addEventListener('change', function() {
        sortBy = this.value;
        applyFiltersAndSort();
    });

    document.getElementById('sortOrder').addEventListener('change', function() {
        sortOrder = this.value;
        applyFiltersAndSort();
    });

    // Folder filter
    document.getElementById('folderFilter').addEventListener('change', function() {
        currentFolder = this.value;
        applyFiltersAndSort();
    });

    // Search
    document.getElementById('searchInput').addEventListener('input', debounce(function() {
        applyFiltersAndSort();
    }, 300));

    // Table header sorting
    document.querySelectorAll('.media-table-view thead th.sortable').forEach(th => {
        th.addEventListener('click', function() {
            const sort = this.dataset.sort;
            if (sortBy === sort) {
                sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy = sort;
                sortOrder = 'asc';
            }
            updateSortUI();
            applyFiltersAndSort();
        });
    });

    // Context menu closing is handled in showContextMenu function

    // Delete from info modal
    document.getElementById('deleteFromInfoModal').addEventListener('click', function() {
        if (contextMenuFile) {
            deleteSingleFile(contextMenuFile);
            bootstrap.Modal.getInstance(document.getElementById('fileInfoModal')).hide();
        }
    });
}

// Load Media Files
async function loadMediaFiles() {
    try {
        showLoading();
        const response = await fetch('/api/admin/media/list');
        const result = await response.json();
        
        if (result.success) {
            mediaFiles = result.data || [];
            organizeByFolders();
            updateFolderFilter();
            applyFiltersAndSort();
            updateStats();
            
            // Ensure the correct view is visible after loading
            switchView(currentView);
        } else {
            showError('Failed to load files: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error loading media:', error);
        showError('Failed to load files. Please try again.');
    } finally {
        hideLoading();
    }
}

// Organize by Folders
function organizeByFolders() {
    const folderMap = {};
    
    mediaFiles.forEach(file => {
        const pathParts = file.path.split('/');
        const folder = pathParts.length > 1 ? pathParts[0] : 'root';
        
        if (!folderMap[folder]) {
            folderMap[folder] = [];
        }
        folderMap[folder].push(file);
    });
    
    folders = Object.keys(folderMap).map(folder => ({
        name: folder,
        files: folderMap[folder]
    }));
}

// Update Folder Filter
function updateFolderFilter() {
    const select = document.getElementById('folderFilter');
    const currentValue = select.value;
    
    select.innerHTML = '<option value="all">All</option>';
    folders.forEach(folder => {
        const option = document.createElement('option');
        option.value = folder.name;
        option.textContent = folder.name + ' (' + folder.files.length + ')';
        select.appendChild(option);
    });
    
    select.value = currentValue || 'all';
    document.getElementById('foldersCount').textContent = folders.length;
}

// Apply Filters and Sort
function applyFiltersAndSort() {
    let filtered = [...mediaFiles];
    
    // Filter by folder
    if (currentFolder !== 'all') {
        filtered = filtered.filter(file => file.path.startsWith(currentFolder + '/'));
    }
    
    // Filter by search
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    if (searchTerm) {
        filtered = filtered.filter(file => 
            file.name.toLowerCase().includes(searchTerm) ||
            file.path.toLowerCase().includes(searchTerm)
        );
    }
    
    // Sort
    filtered.sort((a, b) => {
        let aVal, bVal;
        
        switch(sortBy) {
            case 'name':
                aVal = a.name.toLowerCase();
                bVal = b.name.toLowerCase();
                break;
            case 'size':
                aVal = a.size || 0;
                bVal = b.size || 0;
                break;
            case 'date':
                aVal = new Date(a.modified || 0);
                bVal = new Date(b.modified || 0);
                break;
            case 'type':
                aVal = a.mime_type || '';
                bVal = b.mime_type || '';
                break;
            default:
                aVal = a.name.toLowerCase();
                bVal = b.name.toLowerCase();
        }
        
        if (aVal < bVal) return sortOrder === 'asc' ? -1 : 1;
        if (aVal > bVal) return sortOrder === 'asc' ? 1 : -1;
        return 0;
    });
    
    filteredFiles = filtered;
    renderCurrentView();
    updateStats();
}

// Switch View Mode
function switchView(view) {
    currentView = view;
    
    // Update buttons
    document.querySelectorAll('.view-mode-btn[data-view]').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.view === view);
    });
    
    // Show/hide thumbnail size controls
    document.getElementById('thumbnailSizeGroup').style.display = 
        view === 'thumbnail' ? 'flex' : 'none';
    
    // Show/hide views
    document.getElementById('thumbnailView').style.display = 
        view === 'thumbnail' ? 'block' : 'none';
    document.getElementById('tableView').style.display = 
        view === 'table' ? 'block' : 'none';
    document.getElementById('folderView').style.display = 
        view === 'folder' ? 'block' : 'none';
    
    renderCurrentView();
}

// Set Thumbnail Size
function setThumbnailSize(size) {
    thumbnailSize = size;
    
    document.querySelectorAll('.view-mode-btn[data-size]').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.size === size);
    });
    
    const grid = document.getElementById('thumbnailGrid');
    grid.className = 'media-thumbnail-grid view-' + size;
    
    renderCurrentView();
}

// Render Current View
function renderCurrentView() {
    if (filteredFiles.length === 0) {
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('thumbnailView').style.display = 'none';
        document.getElementById('tableView').style.display = 'none';
        document.getElementById('folderView').style.display = 'none';
        return;
    }
    
    document.getElementById('emptyState').style.display = 'none';
    
    // Ensure the correct view container is visible before rendering
    switch(currentView) {
        case 'thumbnail':
            document.getElementById('thumbnailView').style.display = 'block';
            document.getElementById('tableView').style.display = 'none';
            document.getElementById('folderView').style.display = 'none';
            renderThumbnailView();
            break;
        case 'table':
            document.getElementById('thumbnailView').style.display = 'none';
            document.getElementById('tableView').style.display = 'block';
            document.getElementById('folderView').style.display = 'none';
            renderTableView();
            break;
        case 'folder':
            document.getElementById('thumbnailView').style.display = 'none';
            document.getElementById('tableView').style.display = 'none';
            document.getElementById('folderView').style.display = 'block';
            renderFolderView();
            break;
    }
}

// Render Thumbnail View
function renderThumbnailView() {
    const grid = document.getElementById('thumbnailGrid');
    grid.className = 'media-thumbnail-grid view-' + thumbnailSize;
    
    grid.innerHTML = filteredFiles.map((file, index) => {
        const globalIndex = mediaFiles.findIndex(f => f.path === file.path);
        const isSelected = selectedFiles.has(globalIndex);
        const isImage = file.mime_type && file.mime_type.startsWith('image/');
        const previewUrl = isImage ? file.url : getFileIcon(file.mime_type);
        
        return `
            <div class="media-thumbnail-item ${isSelected ? 'selected' : ''}" 
                 data-index="${globalIndex}"
                 oncontextmenu="event.preventDefault(); event.stopPropagation(); showContextMenu(event, ${globalIndex}); return false;"
                 onclick="if(event.button === 0) toggleSelect(${globalIndex})">
                <img src="${previewUrl}" 
                     alt="${escapeHtml(file.name)}" 
                     class="media-thumbnail-preview"
                     loading="lazy"
                     onerror="this.src='/public/assets/icons/file.png'">
                <div class="media-thumbnail-overlay">
                    <p class="media-thumbnail-name">${escapeHtml(file.name)}</p>
                    <p class="media-thumbnail-size">${formatFileSize(file.size || 0)}</p>
                </div>
            </div>
        `;
    }).join('');
}

// Render Table View
function renderTableView() {
    const tbody = document.getElementById('tableBody');
    
    tbody.innerHTML = filteredFiles.map((file, index) => {
        const globalIndex = mediaFiles.findIndex(f => f.path === file.path);
        const isSelected = selectedFiles.has(globalIndex);
        const isImage = file.mime_type && file.mime_type.startsWith('image/');
        const previewUrl = isImage ? file.url : getFileIcon(file.mime_type);
        
        return `
            <tr class="${isSelected ? 'table-active' : ''}" 
                oncontextmenu="event.preventDefault(); event.stopPropagation(); showContextMenu(event, ${globalIndex}); return false;">
                <td>
                    <input type="checkbox" ${isSelected ? 'checked' : ''} 
                           onchange="toggleSelect(${globalIndex})"
                           onclick="event.stopPropagation()">
                </td>
                <td>
                    <img src="${previewUrl}" 
                         alt="${escapeHtml(file.name)}" 
                         class="media-table-preview"
                         onerror="this.src='/public/assets/icons/file.png'">
                </td>
                <td>
                    <div class="media-table-name">${escapeHtml(file.name)}</div>
                </td>
                <td>
                    <div class="media-table-path">${escapeHtml(file.path)}</div>
                </td>
                <td>${formatFileSize(file.size || 0)}</td>
                <td>${escapeHtml(file.mime_type || 'Unknown')}</td>
                <td>${formatDate(file.modified || '')}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" 
                                onclick="showFileInfo(${globalIndex})"
                                title="Info">
                            <i class="bi bi-info-circle"></i>
                        </button>
                        <button class="btn btn-outline-danger" 
                                onclick="deleteSingleFile(${globalIndex})"
                                title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Render Folder View
function renderFolderView() {
    const container = document.getElementById('folderViewContainer');
    
    let foldersToShow = currentFolder === 'all' ? folders : 
        folders.filter(f => f.name === currentFolder);
    
    container.innerHTML = foldersToShow.map(folder => {
        const folderFiles = folder.files.filter(file => {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            if (!searchTerm) return true;
            return file.name.toLowerCase().includes(searchTerm) ||
                   file.path.toLowerCase().includes(searchTerm);
        });
        
        if (folderFiles.length === 0) return '';
        
        return `
            <div class="media-folder-item" data-folder="${escapeHtml(folder.name)}">
                <div class="media-folder-header" onclick="toggleFolder('${escapeHtml(folder.name)}')">
                    <div class="media-folder-icon">
                        <i class="bi bi-folder-fill"></i>
                    </div>
                    <div class="media-folder-name">${escapeHtml(folder.name)}</div>
                    <div class="media-folder-count">${folderFiles.length} file${folderFiles.length !== 1 ? 's' : ''}</div>
                    <i class="bi bi-chevron-down ms-auto" id="folderIcon-${escapeHtml(folder.name)}"></i>
                </div>
                <div class="media-folder-content collapsed" id="folderContent-${escapeHtml(folder.name)}">
                    ${folderFiles.map((file, index) => {
                        const globalIndex = mediaFiles.findIndex(f => f.path === file.path);
                        const isImage = file.mime_type && file.mime_type.startsWith('image/');
                        const previewUrl = isImage ? file.url : getFileIcon(file.mime_type);
                        
                        return `
                            <div class="media-thumbnail-item" 
                                 data-index="${globalIndex}"
                                 oncontextmenu="event.preventDefault(); event.stopPropagation(); showContextMenu(event, ${globalIndex}); return false;"
                                 onclick="if(event.button === 0) toggleSelect(${globalIndex})">
                                <img src="${previewUrl}" 
                                     alt="${escapeHtml(file.name)}" 
                                     class="media-thumbnail-preview"
                                     loading="lazy"
                                     onerror="this.src='/public/assets/icons/file.png'">
                                <div class="media-thumbnail-overlay">
                                    <p class="media-thumbnail-name">${escapeHtml(file.name)}</p>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }).join('');
}

// Toggle Folder
function toggleFolder(folderName) {
    const content = document.getElementById('folderContent-' + folderName);
    const icon = document.getElementById('folderIcon-' + folderName);
    
    if (content.classList.contains('collapsed')) {
        content.classList.remove('collapsed');
        icon.classList.remove('bi-chevron-down');
        icon.classList.add('bi-chevron-up');
    } else {
        content.classList.add('collapsed');
        icon.classList.remove('bi-chevron-up');
        icon.classList.add('bi-chevron-down');
    }
}

// Setup Context Menu
function setupContextMenu() {
    // Don't prevent default context menu globally
    // Let individual elements handle it via oncontextmenu
}

// Show Context Menu
function showContextMenu(event, fileIndex) {
    event.preventDefault();
    event.stopPropagation();
    
    // Hide any existing context menu first
    hideContextMenu();
    
    contextMenuFile = fileIndex;
    
    const menu = document.getElementById('contextMenu');
    if (!menu) return;
    
    // Get actual menu dimensions after it's rendered
    menu.style.visibility = 'hidden';
    menu.style.display = 'block';
    menu.classList.add('show');
    const menuRect = menu.getBoundingClientRect();
    const menuWidth = menuRect.width;
    const menuHeight = menuRect.height;
    menu.classList.remove('show');
    menu.style.display = '';
    menu.style.visibility = '';
    
    // Get mouse position relative to viewport
    const mouseX = event.clientX;
    const mouseY = event.clientY;
    
    // Calculate position - show menu next to cursor
    let left = mouseX;
    let top = mouseY;
    
    // Add small offset from cursor (10px)
    const offset = 10;
    left += offset;
    top += offset;
    
    // Ensure menu stays within viewport bounds
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    // Adjust horizontal position if menu would overflow
    if (left + menuWidth > viewportWidth) {
        // Show to the left of cursor instead
        left = mouseX - menuWidth - offset;
        // Ensure it doesn't go off-screen
        if (left < 0) {
            left = viewportWidth - menuWidth - 10;
        }
    }
    
    // Adjust vertical position if menu would overflow
    if (top + menuHeight > viewportHeight) {
        // Show above cursor instead
        top = mouseY - menuHeight - offset;
        // Ensure it doesn't go off-screen
        if (top < 0) {
            top = viewportHeight - menuHeight - 10;
        }
    }
    
    // Ensure minimum distance from edges
    left = Math.max(10, Math.min(left, viewportWidth - menuWidth - 10));
    top = Math.max(10, Math.min(top, viewportHeight - menuHeight - 10));
    
    // Set position
    menu.style.left = left + 'px';
    menu.style.top = top + 'px';
    menu.classList.add('show');
    
    // Close menu when clicking outside or pressing Escape
    const closeMenuHandler = (e) => {
        if (!menu.contains(e.target)) {
            hideContextMenu();
            document.removeEventListener('click', closeMenuHandler);
            document.removeEventListener('contextmenu', closeMenuHandler);
        }
    };
    
    const escapeHandler = (e) => {
        if (e.key === 'Escape') {
            hideContextMenu();
            document.removeEventListener('keydown', escapeHandler);
        }
    };
    
    setTimeout(() => {
        document.addEventListener('click', closeMenuHandler);
        document.addEventListener('contextmenu', closeMenuHandler);
        document.addEventListener('keydown', escapeHandler);
    }, 10);
}

// Hide Context Menu
function hideContextMenu() {
    const menu = document.getElementById('contextMenu');
    menu.classList.remove('show');
    contextMenuFile = null;
}

// Toggle Select
function toggleSelect(index) {
    if (selectedFiles.has(index)) {
        selectedFiles.delete(index);
    } else {
        selectedFiles.add(index);
    }
    renderCurrentView();
    updateBulkActions();
    updateStats();
}

// Select All
function selectAll() {
    filteredFiles.forEach((file, index) => {
        const globalIndex = mediaFiles.findIndex(f => f.path === file.path);
        selectedFiles.add(globalIndex);
    });
    renderCurrentView();
    updateBulkActions();
    updateStats();
}

// Clear Selection
function clearSelection() {
    selectedFiles.clear();
    renderCurrentView();
    updateBulkActions();
    updateStats();
}

// Toggle Select All Table
function toggleSelectAllTable(checked) {
    if (checked) {
        filteredFiles.forEach((file, index) => {
            const globalIndex = mediaFiles.findIndex(f => f.path === file.path);
            selectedFiles.add(globalIndex);
        });
    } else {
        filteredFiles.forEach((file, index) => {
            const globalIndex = mediaFiles.findIndex(f => f.path === file.path);
            selectedFiles.delete(globalIndex);
        });
    }
    renderCurrentView();
    updateBulkActions();
    updateStats();
}

// Update Bulk Actions
function updateBulkActions() {
    const bulkActions = document.getElementById('bulkActions');
    const selectedBadge = document.getElementById('selectedBadge');
    
    if (selectedFiles.size > 0) {
        bulkActions.style.display = 'flex';
        selectedBadge.textContent = selectedFiles.size + ' selected';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Update Stats
function updateStats() {
    document.getElementById('totalFiles').textContent = mediaFiles.length;
    
    const totalSize = mediaFiles.reduce((sum, file) => sum + (file.size || 0), 0);
    document.getElementById('totalSize').textContent = formatFileSize(totalSize);
    
    document.getElementById('selectedCount').textContent = selectedFiles.size;
    document.getElementById('foldersCount').textContent = folders.length;
}

// Update Sort UI
function updateSortUI() {
    document.querySelectorAll('.media-table-view thead th.sortable').forEach(th => {
        const sort = th.dataset.sort;
        th.classList.remove('sort-asc', 'sort-desc');
        if (sortBy === sort) {
            th.classList.add('sort-' + sortOrder);
        }
    });
    
    document.getElementById('sortBy').value = sortBy;
    document.getElementById('sortOrder').value = sortOrder;
}

// Show File Info
function showFileInfo(fileIndex) {
    hideContextMenu();
    
    const file = mediaFiles[fileIndex];
    if (!file) return;
    
    const modal = document.getElementById('fileInfoModal');
    const preview = document.getElementById('fileInfoPreview');
    const grid = document.getElementById('fileInfoGrid');
    
    const isImage = file.mime_type && file.mime_type.startsWith('image/');
    const previewUrl = isImage ? file.url : getFileIcon(file.mime_type);
    
    preview.innerHTML = isImage ? 
        `<img src="${previewUrl}" alt="${escapeHtml(file.name)}" class="file-info-preview">` :
        `<div class="file-info-preview" style="display: flex; align-items: center; justify-content: center; background: var(--bg-alt);">
            <i class="bi bi-file-earmark" style="font-size: 4rem; color: var(--muted);"></i>
        </div>`;
    
    grid.innerHTML = `
        <div class="file-info-item">
            <div class="file-info-label">Name</div>
            <div class="file-info-value">${escapeHtml(file.name)}</div>
        </div>
        <div class="file-info-item">
            <div class="file-info-label">Path</div>
            <div class="file-info-value">${escapeHtml(file.path)}</div>
        </div>
        <div class="file-info-item">
            <div class="file-info-label">Size</div>
            <div class="file-info-value">${formatFileSize(file.size || 0)}</div>
        </div>
        <div class="file-info-item">
            <div class="file-info-label">Type</div>
            <div class="file-info-value">${escapeHtml(file.mime_type || 'Unknown')}</div>
        </div>
        <div class="file-info-item">
            <div class="file-info-label">Modified Date</div>
            <div class="file-info-value">${formatDate(file.modified || '')}</div>
        </div>
        <div class="file-info-item">
            <div class="file-info-label">URL</div>
            <div class="file-info-value">
                <a href="${file.url}" target="_blank" class="text-primary">${file.url}</a>
            </div>
        </div>
    `;
    
    contextMenuFile = fileIndex;
    new bootstrap.Modal(modal).show();
}

// Delete Single File
async function deleteSingleFile(fileIndex) {
    hideContextMenu();
    
    const file = mediaFiles[fileIndex];
    if (!file) return;
    
    if (!confirm('Are you sure you want to delete the file "' + file.name + '"?')) {
        return;
    }
    
    try {
        const response = await fetch('/api/admin/media/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ files: [file.path] })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess('File deleted successfully');
            selectedFiles.delete(fileIndex);
            loadMediaFiles();
        } else {
            showError('Failed to delete file: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error deleting file:', error);
        showError('Failed to delete file. Please try again.');
    }
}

// Delete Selected Files
async function deleteSelected() {
    if (selectedFiles.size === 0) return;
    
    const filesToDelete = Array.from(selectedFiles).map(index => mediaFiles[index].path);
    
    if (!confirm('Are you sure you want to delete ' + selectedFiles.size + ' file(s)?')) {
        return;
    }
    
    try {
        const response = await fetch('/api/admin/media/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ files: filesToDelete })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess('Successfully deleted ' + result.deleted + ' file(s)');
            selectedFiles.clear();
            loadMediaFiles();
        } else {
            showError('Failed to delete files: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error deleting files:', error);
        showError('Failed to delete files. Please try again.');
    }
}

// Download File
function downloadFile(fileIndex) {
    hideContextMenu();
    const file = mediaFiles[fileIndex];
    if (file) {
        window.open(file.url, '_blank');
    }
}

// Get File Icon
function getFileIcon(mimeType) {
    if (!mimeType) return '/public/assets/icons/file.png';
    
    const icons = {
        'image/': '/public/assets/icons/image.png',
        'video/': '/public/assets/icons/video.png',
        'audio/': '/public/assets/icons/audio.png',
        'application/pdf': '/public/assets/icons/pdf.png',
        'application/zip': '/public/assets/icons/zip.png',
        'text/': '/public/assets/icons/text.png'
    };
    
    for (const [key, icon] of Object.entries(icons)) {
        if (mimeType.startsWith(key)) {
            return icon;
        }
    }
    
    return '/public/assets/icons/file.png';
}

// Utility Functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function formatDate(dateString) {
    if (!dateString) return 'Not specified';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US') + ' ' + date.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'});
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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

function showLoading() {
    document.getElementById('loadingState').style.display = 'flex';
    document.getElementById('thumbnailView').style.display = 'none';
    document.getElementById('tableView').style.display = 'none';
    document.getElementById('folderView').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';
}

function hideLoading() {
    document.getElementById('loadingState').style.display = 'none';
}

function showSuccess(message) {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
    toast.style.cssText = 'top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 3000);
}

function showError(message) {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    toast.style.cssText = 'top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}
</script>
