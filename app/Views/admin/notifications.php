<?php
/**
 * Admin Notifications Template
 * صفحة إرسال إشعارات النظام للأدمن
 */
?>

<style>
    .users-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 1rem;
        background: var(--bg);
    }
    
    .user-checkbox-item {
        padding: 0.5rem;
        border-radius: 6px;
        transition: background 0.2s ease;
    }
    
    .user-checkbox-item:hover {
        background: var(--card);
    }
    
    .notification-preview {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
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

