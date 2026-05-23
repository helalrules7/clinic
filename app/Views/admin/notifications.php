<?php
/**
 * Admin Notifications Template
 * صفحة إرسال إشعارات النظام للأدمن
 */
?>

<style>
/* Dark/Light Mode Variables - Matching Doctor View */
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

/* Card Styling */
.card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 4px 6px var(--shadow);
    color: var(--text);
    transition: all 0.2s ease;
}

.card:hover {
    box-shadow: 0 8px 25px var(--shadow);
    transform: translateY(-2px);
}

.card-header {
    background: var(--bg-alt);
    border-bottom: 1px solid var(--border);
    border-radius: 12px 12px 0 0;
    padding: 1rem 1.5rem;
    color: var(--text);
}

.card-title {
    color: var(--text);
    font-weight: 600;
}

.card-body {
    padding: 1.5rem;
}

/* Form Controls */
.form-control, .form-select {
    background: var(--card);
    border: 2px solid var(--border);
    color: var(--text);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    background: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.15);
}

.form-control::placeholder {
    color: var(--muted);
}

.form-label {
    color: var(--text);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-text {
    color: var(--muted);
    font-size: 0.875rem;
}

.form-check-input {
    background-color: var(--card);
    border-color: var(--border);
}

.form-check-input:checked {
    background-color: var(--accent);
    border-color: var(--accent);
}

.form-check-label {
    color: var(--text);
}

/* Button Styling */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-primary {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.btn-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    opacity: 0.9;
}

.btn-secondary {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
}

.btn-secondary:hover {
    background-color: var(--border);
    border-color: var(--border);
    color: var(--text);
}

/* Users List */
.users-list {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    background: var(--bg);
}

.users-list::-webkit-scrollbar {
    width: 8px;
}

.users-list::-webkit-scrollbar-track {
    background: var(--bg);
    border-radius: 4px;
}

.users-list::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 4px;
}

.users-list::-webkit-scrollbar-thumb:hover {
    background: var(--muted);
}

.user-checkbox-item {
    padding: 0.75rem;
    border-radius: 8px;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.user-checkbox-item:hover {
    background: var(--card);
    border-color: var(--border);
}

/* Notification Preview */
.notification-preview {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 1.5rem;
}

.notification-preview h6 {
    color: var(--text);
    font-weight: 600;
    margin-bottom: 1rem;
}

.notification-item {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
}

.notification-item.unread {
    border-left: 3px solid var(--accent);
}

.notification-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.notification-item-title {
    color: var(--text);
    font-weight: 600;
    margin: 0;
}

.notification-item-time {
    color: var(--muted);
    font-size: 0.75rem;
}

.notification-item-message {
    color: var(--muted);
    margin: 0;
    font-size: 0.875rem;
}

/* Text Colors */
.text-muted {
    color: var(--muted) !important;
}

/* Alert Styling */
.alert {
    border-radius: 8px;
    border: none;
}

.alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.alert-danger {
    background-color: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}

.alert-warning {
    background-color: rgba(245, 158, 11, 0.1);
    color: var(--warning);
}

.alert-info {
    background-color: rgba(6, 182, 212, 0.1);
    color: var(--info);
}

.dark .alert-success {
    background-color: rgba(74, 222, 128, 0.1);
}

.dark .alert-danger {
    background-color: rgba(251, 113, 133, 0.1);
}

.dark .alert-warning {
    background-color: rgba(251, 191, 36, 0.1);
}

.dark .alert-info {
    background-color: rgba(34, 211, 238, 0.1);
}

.card-header{
    background-color:transparent !important;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bell me-2"></i>
                        Send System Notification
                    </h5>
                </div>
                <div class="card-body">
                    <form id="notificationForm">
                        <div class="mb-3">
                            <label for="notificationType" class="form-label">Notification Type</label>
                            <select class="form-select" id="notificationType" name="type" required>
                                <option value="system">System</option>
                                <option value="info">Information</option>
                                <option value="warning">Warning</option>
                                <option value="success">Success</option>
                                <option value="error">Error</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notificationTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="notificationTitle" name="title" required 
                                   placeholder="Enter notification title">
                        </div>
                        
                        <div class="mb-3">
                            <label for="notificationMessage" class="form-label">Message</label>
                            <textarea class="form-control" id="notificationMessage" name="message" rows="4" required 
                                      placeholder="Enter notification message"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Send To</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="sendTo" id="sendToAll" value="all" checked>
                                <label class="form-check-label" for="sendToAll">
                                    All Users
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sendTo" id="sendToSelected" value="selected">
                                <label class="form-check-label" for="sendToSelected">
                                    Selected Users
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="usersSelection" style="display: none;">
                            <label class="form-label">Select Users</label>
                            <div class="users-list">
                                <?php foreach ($users as $user): ?>
                                    <div class="user-checkbox-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="user_ids[]" 
                                                   value="<?= $user['id'] ?>" id="user_<?= $user['id'] ?>">
                                            <label class="form-check-label" for="user_<?= $user['id'] ?>">
                                                <?= htmlspecialchars($user['name']) ?> 
                                                <small class="text-muted">(<?= $user['role'] ?>)</small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>Send Notification
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="previewNotification()">
                                <i class="bi bi-eye me-2"></i>Preview
                            </button>
                        </div>
                    </form>
                    
                    <div id="notificationPreview" class="notification-preview" style="display: none;">
                        <h6>Preview:</h6>
                        <div id="previewContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle users selection
    document.querySelectorAll('input[name="sendTo"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const usersSelection = document.getElementById('usersSelection');
            if (this.value === 'selected') {
                usersSelection.style.display = 'block';
            } else {
                usersSelection.style.display = 'none';
                // Uncheck all checkboxes
                document.querySelectorAll('input[name="user_ids[]"]').forEach(cb => cb.checked = false);
            }
        });
    });
    
    // Preview notification
    function previewNotification() {
        const type = document.getElementById('notificationType').value;
        const title = document.getElementById('notificationTitle').value;
        const message = document.getElementById('notificationMessage').value;
        
        if (!title || !message) {
            alert('Please fill in title and message');
            return;
        }
        
        const preview = document.getElementById('notificationPreview');
        const previewContent = document.getElementById('previewContent');
        
        previewContent.innerHTML = `
            <div class="notification-item unread">
                <div class="notification-item-header">
                    <h6 class="notification-item-title">${escapeHtml(title)}</h6>
                    <span class="notification-item-time">Just now</span>
                </div>
                <p class="notification-item-message">${escapeHtml(message)}</p>
            </div>
        `;
        
        preview.style.display = 'block';
    }
    
    // Submit form
    document.getElementById('notificationForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const sendTo = formData.get('sendTo');
        const data = {
            type: formData.get('type'),
            title: formData.get('title'),
            message: formData.get('message')
        };
        
        if (sendTo === 'selected') {
            const userIds = Array.from(document.querySelectorAll('input[name="user_ids[]"]:checked'))
                .map(cb => parseInt(cb.value));
            
            if (userIds.length === 0) {
                alert('Please select at least one user');
                return;
            }
            
            data.user_ids = userIds;
        }
        
        try {
            const response = await fetch('/api/notifications/system', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(`Notification sent successfully to ${result.created} user(s)`);
                this.reset();
                document.getElementById('notificationPreview').style.display = 'none';
                document.getElementById('usersSelection').style.display = 'none';
            } else {
                alert('Failed to send notification: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error sending notification:', error);
            alert('Failed to send notification. Please try again.');
        }
    });
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

