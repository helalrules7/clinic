<?php
/**
 * Admin Media Management Template
 */
?>

<style>
    .media-filters {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .media-filters select,
    .media-filters input {
        min-width: 150px;
    }
    
    .folder-section {
        margin-bottom: 2rem;
    }
    
    .folder-header {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 8px 8px 0 0;
        padding: 0.75rem 1rem;
        font-weight: 600;
        color: var(--text);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .folder-content {
        background: var(--card);
        border: 1px solid var(--border);
        border-top: none;
        border-radius: 0 0 8px 8px;
        padding: 1rem;
    }
    
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }
    
    .media-item {
        position: relative;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 6px;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .media-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .media-item.selected {
        border-color: var(--accent);
        box-shadow: 0 0 0 2px var(--accent);
    }
    
    .media-item-checkbox {
        position: absolute;
        top: 0.25rem;
        left: 0.25rem;
        z-index: 10;
        width: 18px;
        height: 18px;
    }
    
    .media-item-preview {
        width: 100%;
        height: 80px;
        object-fit: cover;
        background: var(--bg);
    }
    
    .media-item-info {
        padding: 0.5rem;
    }
    
    .media-item-name {
        font-size: 0.75rem;
        color: var(--text);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .media-item-size {
        font-size: 0.65rem;
        color: var(--muted);
        margin: 0.25rem 0 0 0;
    }
    
    .media-actions {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    
    .media-stats {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .media-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--accent);
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: var(--muted);
        margin-top: 0.25rem;
    }
    
    .pagination-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        margin-top: 1.5rem;
        padding: 1rem;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 8px;
    }
    
    .backup-section {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 2rem;
    }
    
    .backup-table {
        width: 100%;
        margin-top: 1rem;
    }
    
    .backup-table th,
    .backup-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }
    
    .backup-table th {
        background: var(--bg);
        font-weight: 600;
        color: var(--text);
    }
    
    .backup-table td {
        color: var(--text);
    }
    
    .upload-zone {
        border: 2px dashed var(--border);
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        background: var(--bg);
        transition: border-color 0.2s ease, background 0.2s ease;
        cursor: pointer;
    }
    
    .upload-zone:hover {
        border-color: var(--accent);
        background: var(--card);
    }
    
    .upload-zone.dragover {
        border-color: var(--accent);
        background: var(--card);
    }
    
    .modal-content {
        background: var(--card);
        color: var(--text);
    }
    
    .modal-header {
        border-bottom: 1px solid var(--border);
    }
    
    .modal-footer {
        border-top: 1px solid var(--border);
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-images me-2"></i>
                        Media Management
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Statistics -->
                    <div class="media-stats">
                        <div class="media-stats-grid">
                            <div class="stat-item">
                                <div class="stat-value" id="totalFiles">0</div>
                                <div class="stat-label">Total Files</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value" id="totalSize">0 MB</div>
                                <div class="stat-label">Total Size</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value" id="selectedCount">0</div>
                                <div class="stat-label">Selected</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="media-filters">
                        <div>
                            <label for="folderFilter" class="form-label mb-1" style="font-size: 0.875rem;">Folder to Display:</label>
                            <select class="form-select form-select-sm" id="folderFilter" onchange="filterByFolder()">
                                <option value="all">All Folders</option>
                            </select>
                        </div>
                        <div>
                            <label for="perPage" class="form-label mb-1" style="font-size: 0.875rem;">Items Per Page:</label>
                            <select class="form-select form-select-sm" id="perPage" onchange="changePerPage()">
                                <option value="12">12</option>
                                <option value="24" selected>24</option>
                                <option value="48">48</option>
                                <option value="96">96</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="media-actions">
                        <button type="button" class="btn btn-primary" onclick="selectAll()">
                            <i class="bi bi-check-square me-2"></i>Select All
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="deselectAll()">
                            <i class="bi bi-square me-2"></i>Deselect All
                        </button>
                        <button type="button" class="btn btn-danger" onclick="showDeleteSelectedModal()" id="deleteSelectedBtn" disabled>
                            <i class="bi bi-trash me-2"></i>Delete Selected
                        </button>
                        <button type="button" class="btn btn-warning" onclick="showDeleteAllModal()">
                            <i class="bi bi-trash-fill me-2"></i>Delete All
                        </button>
                        <button type="button" class="btn btn-success" onclick="showCreateBackupModal()">
                            <i class="bi bi-download me-2"></i>Create Backup
                        </button>
                    </div>
                    
                    <!-- Media Folders -->
                    <div id="mediaFolders">
                        <div class="text-center py-5">
                            <i class="bi bi-arrow-repeat" style="font-size: 2rem; color: var(--muted);"></i>
                            <p class="mt-2" style="color: var(--muted);">Loading media...</p>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination-container" id="paginationContainer" style="display: none;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="prevPageBtn" onclick="changePage(-1)" disabled>
                            <i class="bi bi-chevron-left"></i> Previous
                        </button>
                        <span id="pageInfo" style="color: var(--text);">Page 1 of 1</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="nextPageBtn" onclick="changePage(1)" disabled>
                            Next <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                    
                    <!-- Backup & Restore Section -->
                    <div class="backup-section">
                        <h6 class="mb-3">
                            <i class="bi bi-archive me-2"></i>Backup & Restore
                        </h6>
                        
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" onclick="showCreateBackupModal()">
                                <i class="bi bi-download me-2"></i>Create New Backup
                            </button>
                        </div>
                        
                        <h6 class="mb-2">Restore from Backup</h6>
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('restoreFile').click()">
                            <i class="bi bi-cloud-upload" style="font-size: 2rem; color: var(--muted);"></i>
                            <p class="mt-2 mb-0" style="color: var(--muted);">
                                Click to upload or drag & drop backup ZIP file
                            </p>
                            <input type="file" id="restoreFile" accept=".zip" style="display: none;" onchange="handleRestoreFile(event)">
                        </div>
                        
                        <h6 class="mb-2 mt-4">Available Backups</h6>
                        <table class="backup-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Size</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="backupTableBody">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Loading backups...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Selected Modal -->
<div class="modal fade" id="deleteSelectedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete Selected Files
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteSelectedCount">0</strong> file(s)?</p>
                <p class="text-danger mb-0"><small><i class="bi bi-info-circle me-1"></i>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSelectedBtn">
                    <i class="bi bi-trash me-2"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete All Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete All Files
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>ALL</strong> media files?</p>
                <p class="text-danger mb-0"><small><i class="bi bi-info-circle me-1"></i>This action cannot be undone. All media files will be permanently deleted.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAllBtn">
                    <i class="bi bi-trash-fill me-2"></i>Delete All
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Backup Modal -->
<div class="modal fade" id="createBackupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-download me-2"></i>Create Backup
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Create a backup of all media files?</p>
                <p class="text-muted mb-0"><small><i class="bi bi-info-circle me-1"></i>This may take a while depending on the number of files.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmCreateBackupBtn">
                    <i class="bi bi-download me-2"></i>Create Backup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Restore Backup Modal -->
<div class="modal fade" id="restoreBackupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-clockwise me-2"></i>Restore Backup
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Restore from backup <strong id="restoreBackupName"></strong>?</p>
                <p class="text-warning mb-0"><small><i class="bi bi-info-circle me-1"></i>This will replace all current media files.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmRestoreBackupBtn">
                    <i class="bi bi-arrow-clockwise me-2"></i>Restore
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let mediaFiles = [];
    let selectedFiles = new Set();
    let currentPage = 1;
    let perPage = 24;
    let currentFolder = 'all';
    let folders = [];
    
    // Load media files on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadMediaFiles();
        loadBackups();
        setupDragAndDrop();
        setupModalHandlers();
    });
    
    // Setup modal handlers
    function setupModalHandlers() {
        // Delete Selected
        document.getElementById('confirmDeleteSelectedBtn').addEventListener('click', function() {
            bootstrap.Modal.getInstance(document.getElementById('deleteSelectedModal')).hide();
            deleteSelected();
        });
        
        // Delete All
        document.getElementById('confirmDeleteAllBtn').addEventListener('click', function() {
            bootstrap.Modal.getInstance(document.getElementById('deleteAllModal')).hide();
            deleteAll();
        });
        
        // Create Backup
        document.getElementById('confirmCreateBackupBtn').addEventListener('click', function() {
            bootstrap.Modal.getInstance(document.getElementById('createBackupModal')).hide();
            createBackup();
        });
        
        // Restore Backup
        document.getElementById('confirmRestoreBackupBtn').addEventListener('click', function() {
            const backupName = document.getElementById('restoreBackupName').textContent;
            bootstrap.Modal.getInstance(document.getElementById('restoreBackupModal')).hide();
            restoreBackup(backupName);
        });
    }
    
    // Show modals
    function showDeleteSelectedModal() {
        if (selectedFiles.size === 0) return;
        document.getElementById('deleteSelectedCount').textContent = selectedFiles.size;
        new bootstrap.Modal(document.getElementById('deleteSelectedModal')).show();
    }
    
    function showDeleteAllModal() {
        new bootstrap.Modal(document.getElementById('deleteAllModal')).show();
    }
    
    function showCreateBackupModal() {
        new bootstrap.Modal(document.getElementById('createBackupModal')).show();
    }
    
    function showRestoreBackupModal(backupName) {
        document.getElementById('restoreBackupName').textContent = backupName;
        new bootstrap.Modal(document.getElementById('restoreBackupModal')).show();
    }
    
    // Load media files
    async function loadMediaFiles() {
        try {
            const response = await fetch('/api/admin/media/list');
            const result = await response.json();
            
            if (result.success) {
                mediaFiles = result.data || [];
                organizeByFolders();
                updateFolderFilter();
                renderMediaFolders();
                updateStats();
            } else {
                showError('Failed to load media files: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error loading media:', error);
            showError('Failed to load media files. Please try again.');
        }
    }
    
    // Organize files by folders
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
    
    // Update folder filter dropdown
    function updateFolderFilter() {
        const select = document.getElementById('folderFilter');
        const currentValue = select.value;
        
        select.innerHTML = '<option value="all">All Folders</option>';
        folders.forEach(folder => {
            const option = document.createElement('option');
            option.value = folder.name;
            option.textContent = folder.name + ' (' + folder.files.length + ')';
            select.appendChild(option);
        });
        
        select.value = currentValue || 'all';
    }
    
    // Filter by folder
    function filterByFolder() {
        currentFolder = document.getElementById('folderFilter').value;
        currentPage = 1;
        renderMediaFolders();
    }
    
    // Change per page
    function changePerPage() {
        perPage = parseInt(document.getElementById('perPage').value);
        currentPage = 1;
        renderMediaFolders();
    }
    
    // Change page
    function changePage(direction) {
        currentPage += direction;
        renderMediaFolders();
    }
    
    // Render media folders
    function renderMediaFolders() {
        const container = document.getElementById('mediaFolders');
        
        let foldersToShow = currentFolder === 'all' ? folders : folders.filter(f => f.name === currentFolder);
        
        if (foldersToShow.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-folder-x" style="font-size: 3rem; color: var(--muted);"></i>
                    <p class="mt-2" style="color: var(--muted);">No media files found</p>
                </div>
            `;
            document.getElementById('paginationContainer').style.display = 'none';
            return;
        }
        
        let html = '';
        let totalFiles = 0;
        
        foldersToShow.forEach(folder => {
            const startIndex = (currentPage - 1) * perPage;
            const endIndex = startIndex + perPage;
            const paginatedFiles = folder.files.slice(startIndex, endIndex);
            totalFiles += folder.files.length;
            
            if (paginatedFiles.length === 0 && currentPage > 1) {
                currentPage = 1;
                return renderMediaFolders();
            }
            
            html += `
                <div class="folder-section">
                    <div class="folder-header">
                        <span><i class="bi bi-folder me-2"></i>${escapeHtml(folder.name)}</span>
                        <span class="badge bg-secondary">${folder.files.length} files</span>
                    </div>
                    <div class="folder-content">
                        <div class="media-grid">
                            ${paginatedFiles.map((file, index) => {
                                const globalIndex = mediaFiles.findIndex(f => f.path === file.path);
                                const isImage = file.mime_type && file.mime_type.startsWith('image/');
                                const previewUrl = isImage ? file.url : '/public/assets/icons/file.png';
                                const fileSize = formatFileSize(file.size || 0);
                                
                                return `
                                    <div class="media-item ${selectedFiles.has(globalIndex) ? 'selected' : ''}" onclick="toggleSelect(${globalIndex})">
                                        <input type="checkbox" class="media-item-checkbox" ${selectedFiles.has(globalIndex) ? 'checked' : ''} 
                                               onclick="event.stopPropagation(); toggleSelect(${globalIndex})">
                                        <img src="${previewUrl}" alt="${escapeHtml(file.name)}" class="media-item-preview" 
                                             onerror="this.src='/public/assets/icons/file.png'">
                                        <div class="media-item-info">
                                            <p class="media-item-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</p>
                                            <p class="media-item-size">${fileSize}</p>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Update pagination
        const totalPages = Math.ceil(totalFiles / perPage);
        document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;
        document.getElementById('prevPageBtn').disabled = currentPage === 1;
        document.getElementById('nextPageBtn').disabled = currentPage >= totalPages;
        document.getElementById('paginationContainer').style.display = totalPages > 1 ? 'flex' : 'none';
    }
    
    // Toggle file selection
    function toggleSelect(index) {
        if (selectedFiles.has(index)) {
            selectedFiles.delete(index);
        } else {
            selectedFiles.add(index);
        }
        renderMediaFolders();
        updateStats();
        updateDeleteButton();
    }
    
    // Select all
    function selectAll() {
        mediaFiles.forEach((_, index) => selectedFiles.add(index));
        renderMediaFolders();
        updateStats();
        updateDeleteButton();
    }
    
    // Deselect all
    function deselectAll() {
        selectedFiles.clear();
        renderMediaFolders();
        updateStats();
        updateDeleteButton();
    }
    
    // Update statistics
    function updateStats() {
        document.getElementById('totalFiles').textContent = mediaFiles.length;
        
        const totalSize = mediaFiles.reduce((sum, file) => sum + (file.size || 0), 0);
        document.getElementById('totalSize').textContent = formatFileSize(totalSize);
        
        document.getElementById('selectedCount').textContent = selectedFiles.size;
    }
    
    // Update delete button state
    function updateDeleteButton() {
        const btn = document.getElementById('deleteSelectedBtn');
        btn.disabled = selectedFiles.size === 0;
    }
    
    // Delete selected files
    async function deleteSelected() {
        if (selectedFiles.size === 0) return;
        
        const filesToDelete = Array.from(selectedFiles).map(index => mediaFiles[index].path);
        
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
                showSuccess(`Successfully deleted ${result.deleted} file(s)`);
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
    
    // Delete all files
    async function deleteAll() {
        try {
            const response = await fetch('/api/admin/media/delete-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                showSuccess(`Successfully deleted all ${result.deleted} file(s)`);
                selectedFiles.clear();
                loadMediaFiles();
            } else {
                showError('Failed to delete all files: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error deleting all files:', error);
            showError('Failed to delete all files. Please try again.');
        }
    }
    
    // Create backup
    async function createBackup() {
        try {
            showLoading('Creating backup...');
            
            const response = await fetch('/api/admin/media/backup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                showSuccess('Backup created successfully!');
                // Reload backups after a short delay to ensure file is written
                setTimeout(() => {
                    loadBackups();
                }, 500);
            } else {
                showError('Failed to create backup: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error creating backup:', error);
            showError('Failed to create backup. Please try again.');
        } finally {
            hideLoading();
        }
    }
    
    // Load backups
    async function loadBackups() {
        try {
            const response = await fetch('/api/admin/media/backups');
            const result = await response.json();
            
            console.log('Backups response:', result); // Debug log
            
            if (result.success) {
                const backups = result.data || [];
                console.log('Backups data:', backups); // Debug log
                renderBackupsTable(backups);
            } else {
                document.getElementById('backupTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-muted">No backups found</td></tr>';
            }
        } catch (error) {
            console.error('Error loading backups:', error);
            document.getElementById('backupTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Failed to load backups: ' + error.message + '</td></tr>';
        }
    }
    
    // Render backups table
    function renderBackupsTable(backups) {
        const tbody = document.getElementById('backupTableBody');
        
        if (backups.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No backups found</td></tr>';
            return;
        }
        
        tbody.innerHTML = backups.map(backup => `
            <tr>
                <td>${escapeHtml(backup.name)}</td>
                <td>${formatFileSize(backup.size)}</td>
                <td>${backup.date}</td>
                <td>
                    <a href="${backup.download_url}" class="btn btn-sm btn-primary me-2" download>
                        <i class="bi bi-download"></i> Download
                    </a>
                    <button type="button" class="btn btn-sm btn-success" onclick="showRestoreBackupModal('${escapeHtml(backup.name)}')">
                        <i class="bi bi-arrow-clockwise"></i> Restore
                    </button>
                </td>
            </tr>
        `).join('');
    }
    
    // Restore backup
    async function restoreBackup(backupName) {
        try {
            showLoading('Restoring backup...');
            
            const response = await fetch('/api/admin/media/restore', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ backup: backupName })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showSuccess('Backup restored successfully!');
                loadMediaFiles();
            } else {
                showError('Failed to restore backup: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error restoring backup:', error);
            showError('Failed to restore backup. Please try again.');
        } finally {
            hideLoading();
        }
    }
    
    // Handle restore file upload
    function handleRestoreFile(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        if (!file.name.endsWith('.zip')) {
            showError('Please upload a ZIP file');
            return;
        }
        
        showRestoreBackupModal(file.name);
        
        // Store file for later use
        window.uploadedBackupFile = file;
    }
    
    // Setup drag and drop
    function setupDragAndDrop() {
        const uploadZone = document.getElementById('uploadZone');
        
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });
        
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            const file = e.dataTransfer.files[0];
            if (file && file.name.endsWith('.zip')) {
                const input = document.getElementById('restoreFile');
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
                handleRestoreFile({ target: input });
            } else {
                showError('Please drop a ZIP file');
            }
        });
    }
    
    // Utility functions
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function showSuccess(message) {
        alert('Success: ' + message);
    }
    
    function showError(message) {
        alert('Error: ' + message);
    }
    
    function showLoading(message) {
        console.log('Loading: ' + message);
    }
    
    function hideLoading() {
        console.log('Loading complete');
    }
</script>
