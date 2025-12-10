<?php
/**
 * Admin Users Management Template
 * قالب إدارة المستخدمين
 */
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Users Management
                    </h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="fas fa-plus me-2"></i>
                        Add New User
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Search and Filter -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" 
                                   placeholder="Search by name, username, or email..." 
                                   value="<?= htmlspecialchars($search ?? '') ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select" onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="doctor" <?= ($role ?? '') === 'doctor' ? 'selected' : '' ?>>Doctor</option>
                            <option value="secretary" <?= ($role ?? '') === 'secretary' ? 'selected' : '' ?>>Secretary</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <a href="/admin/users" class="btn btn-outline-secondary">
                            <i class="fas fa-refresh me-2"></i>
                            Reset
                        </a>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Appointments</th>
                                <th>Patients</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($user['name']) ?></h6>
                                                    <small class="text-muted">ID: <?= $user['id'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">@<?= htmlspecialchars($user['username']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'doctor' ? 'primary' : 'info') ?>">
                                                <?= $user['role'] === 'admin' ? 'Admin' : ($user['role'] === 'doctor' ? 'Doctor' : 'Secretary') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $user['is_active'] ? 'success' : 'secondary' ?>">
                                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= number_format($user['total_appointments'] ?? 0) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?= number_format($user['total_patients'] ?? 0) ?></span>
                                        </td>
                                        <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No users found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/users">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" required pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="20">
                        <div class="form-text">Username must be 3-20 characters, letters, numbers, and underscores only</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role *</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="doctor">Doctor</option>
                            <option value="secretary">Secretary</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="specializationField" style="display: none;">
                        <label for="specialization" class="form-label">Specialization</label>
                        <input type="text" class="form-control" id="specialization" name="specialization" placeholder="e.g. Ophthalmology">
                    </div>
                    
                    <div class="mb-3" id="licenseField" style="display: none;">
                        <label for="license_number" class="form-label">License Number</label>
                        <input type="text" class="form-control" id="license_number" name="license_number">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                        <div class="form-text">Password must be at least 8 characters</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editUserForm">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="20">
                        <div class="form-text">Username must be 3-20 characters, letters, numbers, and underscores only</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role *</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="doctor">Doctor</option>
                            <option value="secretary">Secretary</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the user <strong id="delete_user_name"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteUserForm" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Dark/Light Mode Variables - Matching Doctor View */
:root {
    --bg: #f8fafc;
    --bg-alt: #f1f5f9;
    --bg-dark: #ffffff;
    --text: #0f172a;
    --text-muted: #475569;
    --accent: #0ea5e9;
    --accent-rgb: 14, 165, 233;
    --border: #e2e8f0;
    --muted: #475569;
    --card: #ffffff;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --info: #06b6d4;
    --shadow: rgba(0, 0, 0, 0.1);
}

.dark {
    --bg: #0b1220;
    --bg-alt: #1e293b;
    --bg-dark: #1e293b;
    --text: #f8fafc;
    --text-muted: #94a3b8;
    --accent: #38bdf8;
    --accent-rgb: 56, 189, 248;
    --border: #334155;
    --muted: #94a3b8;
    --card: #1e293b;
    --success: #4ade80;
    --danger: #fb7185;
    --warning: #fbbf24;
    --info: #22d3ee;
    --shadow: rgba(0, 0, 0, 0.3);
}

.card {
    background-color: var(--card);
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
    background-color: var(--bg-alt);
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

.table {
    background-color: var(--card);
    color: var(--text);
    border-radius: 8px;
    overflow: hidden;
}

.table thead th {
    background-color: var(--bg-alt) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
    font-weight: 600;
    padding: 1rem;
}

.table-dark th {
    background-color: var(--bg-alt) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.table tbody tr {
    background-color: var(--card);
    border-color: var(--border);
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: var(--bg-alt);
}

.table td {
    background-color: transparent;
    border-color: var(--border);
    color: var(--text);
    padding: 1rem;
    vertical-align: middle;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 1.2rem;
    font-weight: 600;
    background: linear-gradient(135deg, var(--accent), var(--info));
}

.btn-group .btn {
    margin: 0 2px;
}

.text-muted {
    color: var(--muted) !important;
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

.btn-outline-primary {
    color: var(--accent);
    border-color: var(--accent);
}

.btn-outline-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.btn-outline-danger {
    color: var(--danger);
    border-color: var(--danger);
}

.btn-outline-danger:hover {
    background-color: var(--danger);
    border-color: var(--danger);
    color: white;
}

.btn-outline-secondary {
    color: var(--muted);
    border-color: var(--border);
}

.btn-outline-secondary:hover {
    background-color: var(--bg-alt);
    border-color: var(--accent);
    color: var(--accent);
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

.btn-danger {
    background-color: var(--danger);
    border-color: var(--danger);
    color: white;
}

.btn-danger:hover {
    background-color: var(--danger);
    border-color: var(--danger);
    opacity: 0.9;
}

/* Badge Styling */
.badge {
    border-radius: 6px;
    font-weight: 500;
    padding: 0.35rem 0.65rem;
}

.badge.bg-primary {
    background-color: var(--accent) !important;
    color: white;
}

.badge.bg-success {
    background-color: var(--success) !important;
    color: white;
}

.badge.bg-danger {
    background-color: var(--danger) !important;
    color: white;
}

.badge.bg-info {
    background-color: var(--info) !important;
    color: white;
}

.badge.bg-warning {
    background-color: var(--warning) !important;
    color: #000;
}

.badge.bg-secondary {
    background-color: var(--muted) !important;
    color: white;
}

/* Modal styling */
.modal-content {
    background-color: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: var(--text);
    box-shadow: 0 25px 50px -12px var(--shadow);
}

.modal-header {
    background-color: var(--bg-alt);
    border-bottom: 1px solid var(--border);
    border-radius: 12px 12px 0 0;
    padding: 1rem 1.5rem;
    color: var(--text);
}

.modal-title {
    color: var(--text);
    font-weight: 600;
}

.modal-body {
    padding: 1.5rem;
    color: var(--text);
}

.modal-footer {
    background-color: var(--bg-alt);
    border-top: 1px solid var(--border);
    border-radius: 0 0 12px 12px;
    padding: 1rem 1.5rem;
}

/* Form Controls */
.form-control {
    background-color: var(--card);
    border: 2px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    padding: 0.75rem 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.form-control:focus {
    background-color: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.15);
}

.form-control::placeholder {
    color: var(--muted);
}

.form-select {
    background-color: var(--card);
    border: 2px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    padding: 0.75rem 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.form-select:focus {
    background-color: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.15);
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

/* Pagination Styling */
.pagination .page-link {
    background-color: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    padding: 0.5rem 0.75rem;
    margin: 0 0.125rem;
    transition: all 0.2s ease;
}

.pagination .page-link:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
    transform: translateY(-1px);
}

.pagination .page-item.active .page-link {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.pagination .page-item.disabled .page-link {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--muted);
    opacity: 0.6;
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

.dark .alert-success {
    background-color: rgba(74, 222, 128, 0.1);
}

.dark .alert-danger {
    background-color: rgba(251, 113, 133, 0.1);
}

/* Custom Scrollbar */
.table-responsive::-webkit-scrollbar {
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: var(--bg);
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: var(--muted);
}

/* Animation */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeUp 0.35s ease both;
}

/* Dark mode shadow adjustment */
.dark .card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
}
</style>

<script>
// Show/hide specialization fields based on role
document.getElementById('role').addEventListener('change', function() {
    const specializationField = document.getElementById('specializationField');
    const licenseField = document.getElementById('licenseField');
    
    if (this.value === 'doctor') {
        specializationField.style.display = 'block';
        licenseField.style.display = 'block';
    } else {
        specializationField.style.display = 'none';
        licenseField.style.display = 'none';
    }
});

// Edit user function
function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_is_active').checked = user.is_active == 1;
    
    document.getElementById('editUserForm').action = '/admin/users/update/' + user.id;
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

// Delete user function
function deleteUser(userId, userName) {
    document.getElementById('delete_user_name').textContent = userName;
    document.getElementById('deleteUserForm').action = '/admin/users/delete/' + userId;
    
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}
</script>

