<?php
require_once '../db.php';

if (!isSuperAdmin()) {
    echo '<div class="alert alert-danger">Only Super Admins can access this section.</div>';
    exit;
}

require_once __DIR__ . '/../classes/AdminService.php';
$adminService = new AdminService();

$admins = $adminService->getAllAdmins();
?>

<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold"><i class="bi bi-people"></i> Admin Management</h2>
            <p class="text-muted">Manage SSC administrators</p>
        </div>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createAdminModal">
            <i class="bi bi-plus-circle"></i> Create New Admin
        </button>
    </div>
    
    <!-- Admins Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive-custom">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th style="width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($admin['name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td><?php echo htmlspecialchars($admin['phone_number']); ?></td>
                                <td>
                                    <span class="badge bg-primary">
                                        SSC Admin
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editAdmin(<?php echo htmlspecialchars(json_encode($admin)); ?>)">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteAdmin(<?php echo $admin['admin_id']; ?>)">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Admin Modal -->
<div class="modal fade" id="createAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus"></i> Create New SSC Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="./process/process_profile.php" id="createAdminForm">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="role" value="ssc_admin">
                <div class="modal-body">
                    <div id="createErrors" class="alert alert-danger d-none" role="alert"></div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_name" name="name" required 
                               minlength="2" maxlength="100">
                        <small class="text-muted">Minimum 2 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="create_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="create_phone" name="phone" placeholder="09123456789" required 
                               pattern="^(09|\+639)\d{9}$">
                        <small class="text-muted">Format: 09123456789 or +639123456789</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="create_password" name="password" required 
                               minlength="6" maxlength="50">
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> New admin will be created with SSC Admin role
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Create Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Admin Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil"></i> Edit Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="./process/process_profile.php" id="editAdminForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="role" value="ssc_admin">
                <div class="modal-body">
                    <div id="editErrors" class="alert alert-danger d-none" role="alert"></div>
                    
                    <input type="hidden" id="edit_admin_id" name="admin_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required 
                               minlength="2" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="tel" class="form-control" id="edit_phone" name="phone" required 
                               pattern="^(09|\+639)\d{9}$">
                        <small class="text-muted">Format: 09123456789 or +639123456789</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Role: SSC Admin (cannot be changed)
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>