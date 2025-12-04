<?php
/**
 * Admin Database Backup & Restore Template
 * صفحة نسخ احتياطي واستعادة قاعدة البيانات للأدمن
 */
?>

<style>
    .backup-section {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .backup-section h6 {
        color: var(--text);
        margin-bottom: 1rem;
        font-weight: 600;
    }
    
    .backup-type-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
    }
    
    .backup-type-card {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 1.5rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .backup-type-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
    
    .backup-list {
        margin-top: 1rem;
    }
    
    .backup-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 6px;
        margin-bottom: 0.5rem;
    }
    
    .backup-item-info {
        flex: 1;
    }
    
    .backup-item-name {
        font-weight: 500;
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
    
    .upload-zone {
        border: 2px dashed var(--border);
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        background: var(--bg);
        transition: border-color 0.2s ease, background 0.2s ease;
        cursor: pointer;
        margin-top: 1rem;
    }
    
    .upload-zone:hover {
        border-color: var(--accent);
        background: var(--card);
    }
    
    .upload-zone.dragover {
        border-color: var(--accent);
        background: var(--card);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 1rem;
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
        if (!confirm('Create a database backup? This may take a while.')) {
            return;
        }
        
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
                loadBackups();
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
    
    // Create full backup
    async function createFullBackup() {
        if (!confirm('Create a full backup (database + media)? This may take a while.')) {
            return;
        }
        
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
                loadBackups();
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
    
    // Create website backup
    async function createWebsiteBackup() {
        if (!confirm('Create a website backup (entire public_html + database)? This may take a very long time.')) {
            return;
        }
        
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
                loadBackups();
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
    
    // Restore backup
    async function restoreBackup(backupName, type) {
        if (!confirm(`Restore from backup "${backupName}"? This will replace the current database.`)) {
            return;
        }
        
        if (!confirm('WARNING: This will overwrite your current database. Are you absolutely sure?')) {
            return;
        }
        
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
        }
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
        
        if (!confirm(`Restore from uploaded backup "${file.name}"? This will replace the current database.`)) {
            return;
        }
        
        if (!confirm('WARNING: This will overwrite your current database. Are you absolutely sure?')) {
            return;
        }
        
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
        console.log('Loading: ' + message);
    }
    
    function hideLoading() {
        console.log('Loading complete');
    }
</script>

