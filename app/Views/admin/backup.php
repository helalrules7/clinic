<?php
/**
 * Admin Database Backup & Restore Template
 * صفحة نسخ احتياطي واستعادة قاعدة البيانات للأدمن
 */
?>

<style>
/* CSS Variables for Dark/Light Mode - Matching Doctor View */
:root {
    --warning: #f59e0b;
    --info: #06b6d4;
    --shadow: rgba(0, 0, 0, 0.1);
}

.dark {
    --warning: #fbbf24;
    --info: #22d3ee;
    --shadow: rgba(0, 0, 0, 0.3);
}

/* Card Styles */
.card {
    background-color: var(--card) !important;
    border: 1px solid var(--border) !important;
    border-radius: 12px;
    box-shadow: 0 4px 6px var(--shadow) !important;
    color: var(--text);
    transition: all 0.2s ease;
}

.card:hover {
    box-shadow: 0 8px 25px var(--shadow) !important;
    transform: translateY(-2px);
}

.card-header {
    background-color: transparent !important;
    border-bottom: 1px solid var(--border) !important;
    color: var(--text);
    padding: 1rem 1.5rem;
}

.card-body {
    background-color: transparent !important;
    padding: 1.5rem;
}

.card-title {
    color: var(--text) !important;
    font-weight: 600;
}

/* Backup Section */
.backup-section {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px var(--shadow);
}

.backup-section h6 {
    color: var(--text);
    margin-bottom: 1rem;
    font-weight: 600;
}

/* Backup Type Grid */
.backup-type-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.backup-type-card {
    background: var(--bg-alt);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.backup-type-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px var(--shadow);
    border-color: var(--accent);
}

.backup-type-icon {
    font-size: 2.5rem;
    color: var(--accent);
    margin-bottom: 1rem;
}

.backup-type-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.5rem;
}

.backup-type-description {
    font-size: 0.875rem;
    color: var(--muted);
    margin-bottom: 1rem;
}

/* Backup List */
.backup-list {
    margin-top: 1rem;
}

.backup-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--bg-alt);
    border: 1px solid var(--border);
    border-radius: 10px;
    margin-bottom: 0.75rem;
    transition: all 0.2s ease;
}

.backup-item:hover {
    background: var(--card);
    border-color: var(--accent);
    transform: translateX(4px);
}

.backup-item-info {
    flex: 1;
}

.backup-item-name {
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.backup-item-details {
    font-size: 0.875rem;
    color: var(--muted);
    margin: 0.25rem 0 0 0;
}

.backup-item-actions {
    display: flex;
    gap: 0.5rem;
}

/* Upload Zone */
.upload-zone {
    border: 2px dashed var(--border);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    background: var(--bg-alt);
    transition: all 0.3s ease;
    cursor: pointer;
    margin-top: 1rem;
}

.upload-zone:hover {
    border-color: var(--accent);
    background: var(--card);
    transform: scale(1.01);
}

.upload-zone.dragover {
    border-color: var(--accent);
    background: rgba(var(--accent-rgb), 0.1);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px var(--shadow);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px var(--shadow);
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--accent);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--muted);
    margin-top: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Text Colors */
.text-muted {
    color: var(--muted) !important;
}

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-primary {
    background-color: var(--accent) !important;
    border-color: var(--accent) !important;
}

.btn-primary:hover {
    background-color: #0284c7 !important;
    border-color: #0284c7 !important;
}

.btn-success {
    background-color: var(--success) !important;
    border-color: var(--success) !important;
}

.btn-success:hover {
    background-color: #059669 !important;
    border-color: #059669 !important;
}

.btn-warning {
    background-color: var(--warning) !important;
    border-color: var(--warning) !important;
    color: #1e293b !important;
}

.btn-warning:hover {
    background-color: #d97706 !important;
    border-color: #d97706 !important;
}

.btn-danger {
    background-color: var(--danger) !important;
    border-color: var(--danger) !important;
}

.btn-danger:hover {
    background-color: #dc2626 !important;
    border-color: #dc2626 !important;
}

.btn-secondary {
    background-color: var(--bg-alt) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.btn-secondary:hover {
    background-color: var(--border) !important;
}

/* Modals */
.modal-content {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
    border-radius: 12px;
}

.modal-header {
    border-bottom-color: var(--border) !important;
}

.modal-footer {
    border-top-color: var(--border) !important;
}

.modal-title {
    color: var(--text) !important;
}

/* Form Controls */
.form-control,
.form-select {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
    border-radius: 8px;
}

.form-control:focus,
.form-select:focus {
    background-color: var(--card) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25) !important;
}

/* Alerts */
.alert {
    border-radius: 10px;
}

.alert-info {
    background-color: rgba(var(--accent-rgb), 0.15) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
}

.alert-success {
    background-color: rgba(16, 185, 129, 0.15) !important;
    border-color: var(--success) !important;
    color: var(--text) !important;
}

.alert-warning {
    background-color: rgba(245, 158, 11, 0.15) !important;
    border-color: var(--warning) !important;
    color: var(--text) !important;
}

.alert-danger {
    background-color: rgba(239, 68, 68, 0.15) !important;
    border-color: var(--danger) !important;
    color: var(--text) !important;
}

/* Progress Bar */
.progress {
    background-color: var(--bg-alt) !important;
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--bg-alt);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: var(--muted);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--accent);
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-database me-2"></i>
                        Database Backup & Restore
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Statistics -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value" id="dbBackupCount">0</div>
                            <div class="stat-label">Database Backups</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value" id="fullBackupCount">0</div>
                            <div class="stat-label">Full Backups</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value" id="websiteBackupCount">0</div>
                            <div class="stat-label">Website Backups</div>
                        </div>
                    </div>
                    
                    <!-- Backup Types -->
                    <div class="backup-type-grid">
                        <!-- Database Backup -->
                        <div class="backup-type-card">
                            <div class="backup-type-icon">
                                <i class="bi bi-database"></i>
                            </div>
                            <div class="backup-type-title">Database Backup</div>
                            <div class="backup-type-description">
                                Backup only the database. Fast and lightweight.
                            </div>
                            <button type="button" class="btn btn-primary w-100" onclick="createDatabaseBackup()">
                                <i class="bi bi-download me-2"></i>Create Database Backup
                            </button>
                        </div>
                        
                        <!-- Full Backup -->
                        <div class="backup-type-card">
                            <div class="backup-type-icon">
                                <i class="bi bi-archive"></i>
                            </div>
                            <div class="backup-type-title">Full Backup</div>
                            <div class="backup-type-description">
                                Backup database + all media files and uploads.
                            </div>
                            <button type="button" class="btn btn-success w-100" onclick="createFullBackup()">
                                <i class="bi bi-download me-2"></i>Create Full Backup
                            </button>
                        </div>
                        
                        <!-- Website Backup -->
                        <div class="backup-type-card">
                            <div class="backup-type-icon">
                                <i class="bi bi-globe"></i>
                            </div>
                            <div class="backup-type-title">Website Backup</div>
                            <div class="backup-type-description">
                                Backup entire website (public_html) + database.
                            </div>
                            <button type="button" class="btn btn-warning w-100" onclick="createWebsiteBackup()">
                                <i class="bi bi-download me-2"></i>Create Website Backup
                            </button>
                        </div>
                    </div>
                    
                    <!-- Database Backups List -->
                    <div class="backup-section">
                        <h6>
                            <i class="bi bi-database me-2"></i>Database Backups
                        </h6>
                        <div class="backup-list" id="databaseBackupsList">
                            <p class="text-muted">Loading...</p>
                        </div>
                    </div>
                    
                    <!-- Full Backups List -->
                    <div class="backup-section">
                        <h6>
                            <i class="bi bi-archive me-2"></i>Full Backups
                        </h6>
                        <div class="backup-list" id="fullBackupsList">
                            <p class="text-muted">Loading...</p>
                        </div>
                    </div>
                    
                    <!-- Website Backups List -->
                    <div class="backup-section">
                        <h6>
                            <i class="bi bi-globe me-2"></i>Website Backups
                        </h6>
                        <div class="backup-list" id="websiteBackupsList">
                            <p class="text-muted">Loading...</p>
                        </div>
                    </div>
                    
                    <!-- Restore Section -->
                    <div class="backup-section">
                        <h6>
                            <i class="bi bi-arrow-clockwise me-2"></i>Restore Database
                        </h6>
                        <p class="text-muted mb-3">Upload a database backup file to restore</p>
                        <div class="upload-zone" id="restoreUploadZone" onclick="document.getElementById('restoreFile').click()">
                            <i class="bi bi-cloud-upload" style="font-size: 2rem; color: var(--muted);"></i>
                            <p class="mt-2 mb-0" style="color: var(--muted);">
                                Click to upload or drag & drop database backup file (.sql or .zip)
                            </p>
                            <input type="file" id="restoreFile" accept=".sql,.zip" style="display: none;" onchange="handleRestoreFile(event)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Load backups on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadBackups();
        setupDragAndDrop();
    });
    
    // Load all backups
    async function loadBackups() {
        try {
            const response = await fetch('/api/admin/backup/list');
            const result = await response.json();
            
            if (result.success) {
                const backups = result.data || {};
                
                // Update counts
                document.getElementById('dbBackupCount').textContent = (backups.database || []).length;
                document.getElementById('fullBackupCount').textContent = (backups.full || []).length;
                document.getElementById('websiteBackupCount').textContent = (backups.website || []).length;
                
                // Render lists
                renderBackups('databaseBackupsList', backups.database || [], 'database');
                renderBackups('fullBackupsList', backups.full || [], 'full');
                renderBackups('websiteBackupsList', backups.website || [], 'website');
            } else {
                showError('Failed to load backups: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error loading backups:', error);
            showError('Failed to load backups. Please try again.');
        }
    }
    
    // Render backups list
    function renderBackups(containerId, backups, type) {
        const container = document.getElementById(containerId);
        
        if (backups.length === 0) {
            container.innerHTML = '<p class="text-muted">No backups found</p>';
            return;
        }
        
        container.innerHTML = backups.map(backup => `
            <div class="backup-item">
                <div class="backup-item-info">
                    <p class="backup-item-name">${escapeHtml(backup.name)}</p>
                    <p class="backup-item-details">
                        ${formatFileSize(backup.size)} • ${backup.date}
                        ${backup.db_size ? ' • DB: ' + formatFileSize(backup.db_size) : ''}
                        ${backup.media_size ? ' • Media: ' + formatFileSize(backup.media_size) : ''}
                    </p>
                </div>
                <div class="backup-item-actions">
                    <a href="${backup.download_url}" class="btn btn-sm btn-primary" download>
                        <i class="bi bi-download"></i> Download
                    </a>
                    ${type === 'database' ? `
                        <button type="button" class="btn btn-sm btn-success" onclick="restoreBackup('${escapeHtml(backup.name)}', 'database')">
                            <i class="bi bi-arrow-clockwise"></i> Restore
                        </button>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }
    
    // Create database backup
    async function createDatabaseBackup() {
        showCreateDatabaseBackupModal();
    }
    
    function showCreateDatabaseBackupModal() {
        const modal = document.getElementById('createDatabaseBackupModal');
        if (!modal) {
            const modalHtml = `
                <div class="modal fade" id="createDatabaseBackupModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-database me-2"></i>Create Database Backup
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Create a database backup? This may take a while.</p>
                                <p class="text-muted mb-0"><small><i class="bi bi-info-circle me-1"></i>The backup will be compressed and saved automatically.</small></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="confirmCreateDatabaseBackupBtn">
                                    <i class="bi bi-download me-2"></i>Create Backup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        
        const modalInstance = new bootstrap.Modal(document.getElementById('createDatabaseBackupModal'));
        modalInstance.show();
        
        // Setup confirm button
        const confirmBtn = document.getElementById('confirmCreateDatabaseBackupBtn');
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        newConfirmBtn.addEventListener('click', async function() {
            modalInstance.hide();
            
            try {
                showLoading('Creating database backup...');
                
                const response = await fetch('/api/admin/backup/database', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Database backup created successfully!');
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
        });
    }
    
    // Create full backup
    async function createFullBackup() {
        showCreateFullBackupModal();
    }
    
    function showCreateFullBackupModal() {
        const modal = document.getElementById('createFullBackupModal');
        if (!modal) {
            const modalHtml = `
                <div class="modal fade" id="createFullBackupModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-archive me-2"></i>Create Full Backup
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Create a full backup (database + media)? This may take a while.</p>
                                <p class="text-muted mb-0"><small><i class="bi bi-info-circle me-1"></i>This will backup both the database and all media files.</small></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-success" id="confirmCreateFullBackupBtn">
                                    <i class="bi bi-download me-2"></i>Create Backup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        
        const modalInstance = new bootstrap.Modal(document.getElementById('createFullBackupModal'));
        modalInstance.show();
        
        const confirmBtn = document.getElementById('confirmCreateFullBackupBtn');
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        newConfirmBtn.addEventListener('click', async function() {
            modalInstance.hide();
            
            try {
                showLoading('Creating full backup...');
                
                const response = await fetch('/api/admin/backup/full', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Full backup created successfully!');
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
        });
    }
    
    // Create website backup
    async function createWebsiteBackup() {
        showCreateWebsiteBackupModal();
    }
    
    function showCreateWebsiteBackupModal() {
        const modal = document.getElementById('createWebsiteBackupModal');
        if (!modal) {
            const modalHtml = `
                <div class="modal fade" id="createWebsiteBackupModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title">
                                    <i class="bi bi-globe me-2"></i>Create Website Backup
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Create a website backup (entire public_html + database)?</p>
                                <p class="text-warning mb-0"><small><i class="bi bi-info-circle me-1"></i>This may take a very long time depending on the website size.</small></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-warning" id="confirmCreateWebsiteBackupBtn">
                                    <i class="bi bi-download me-2"></i>Create Backup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        
        const modalInstance = new bootstrap.Modal(document.getElementById('createWebsiteBackupModal'));
        modalInstance.show();
        
        const confirmBtn = document.getElementById('confirmCreateWebsiteBackupBtn');
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        newConfirmBtn.addEventListener('click', async function() {
            modalInstance.hide();
            
            try {
                showLoading('Creating website backup... This may take several minutes.');
                
                const response = await fetch('/api/admin/backup/website', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Website backup created successfully!');
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
        });
    }
    
    // Restore backup
    async function restoreBackup(backupName, type) {
        showRestoreBackupModal(backupName, type);
    }
    
    function showRestoreBackupModal(backupName, type) {
        const modalId = 'restoreBackupModal_' + Date.now();
        const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title">
                                <i class="bi bi-arrow-clockwise me-2"></i>Restore Database
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Restore from backup <strong>${escapeHtml(backupName)}</strong>?</p>
                            <p class="text-danger mb-0"><small><i class="bi bi-exclamation-triangle me-1"></i>WARNING: This will overwrite your current database. This action cannot be undone.</small></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-warning" id="confirmRestoreBtn_${modalId}">
                                <i class="bi bi-arrow-clockwise me-2"></i>Restore
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modalInstance = new bootstrap.Modal(document.getElementById(modalId));
        modalInstance.show();
        
        document.getElementById(`confirmRestoreBtn_${modalId}`).addEventListener('click', async function() {
            modalInstance.hide();
            
            try {
                showLoading('Restoring database...');
                
                const response = await fetch('/api/admin/backup/restore', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ backup: backupName, type: type })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Database restored successfully!');
                } else {
                    showError('Failed to restore backup: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error restoring backup:', error);
                showError('Failed to restore backup. Please try again.');
            } finally {
                hideLoading();
                // Remove modal from DOM
                setTimeout(() => {
                    const modalElement = document.getElementById(modalId);
                    if (modalElement) {
                        modalElement.remove();
                    }
                }, 300);
            }
        });
        
        // Remove modal on hide
        document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }
    
    // Handle restore file upload
    function handleRestoreFile(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        const validExtensions = ['.sql', '.zip'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!validExtensions.includes(fileExtension)) {
            showError('Please upload a .sql or .zip file');
            return;
        }
        
        showRestoreUploadModal(file.name, file);
        
        const formData = new FormData();
        formData.append('backup', file);
        
        showLoading('Uploading and restoring database...');
        
        fetch('/api/admin/backup/restore-upload', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showSuccess('Database restored successfully!');
                event.target.value = '';
            } else {
                showError('Failed to restore backup: ' + (result.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error restoring backup:', error);
            showError('Failed to restore backup. Please try again.');
        })
        .finally(() => {
            hideLoading();
        });
    }
    
    // Setup drag and drop
    function setupDragAndDrop() {
        const uploadZone = document.getElementById('restoreUploadZone');
        
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
            if (file) {
                const validExtensions = ['.sql', '.zip'];
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                
                if (validExtensions.includes(fileExtension)) {
                    const input = document.getElementById('restoreFile');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                    handleRestoreFile({ target: input });
                } else {
                    showError('Please drop a .sql or .zip file');
                }
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
    }
    
    function hideLoading() {
    }
</script>

