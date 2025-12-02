<?php
require_once '../db.php';

if (!isSuperAdmin()) {
    echo '<div class="alert alert-danger">Only Super Admins can access this section.</div>';
    exit;
}

require_once __DIR__ . '/../classes/AdminService.php';

$adminService = new AdminService();
$admins = $adminService->getAllAdmins();

$database = new Database();
$db = $database->getConnection();
$all_admins_query = "SELECT admin_id, name, email, phone_number, role, is_active, created_at 
                     FROM admin 
                     ORDER BY 
                         CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END,
                         created_at DESC";
$all_admins_result = mysqli_query($db, $all_admins_query);
$all_admins = [];
while ($row = mysqli_fetch_assoc($all_admins_result)) {
    $all_admins[] = $row;
}
mysqli_free_result($all_admins_result);
?>

<link rel="stylesheet" href="css/profile.css">

<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-danger"><i class="bi bi-people"></i> Admin Management</h2>
            <p class="text-muted">Manage system administrators (<?php echo count($all_admins); ?> total)</p>
        </div>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createAdminModal">
            <i class="bi bi-plus-circle"></i> Add New Admin
        </button>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive-custom">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th style="width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_admins)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #e5e7eb;"></i>
                                    <p class="text-muted mt-3 mb-2 fw-bold">No Admins Found</p>
                                    <p class="text-muted mb-0">Click "Add New Admin" to add your first administrator.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_admins as $admin): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-white text-dark border">ID #<?php echo str_pad($admin['admin_id'], 5, '0', STR_PAD_LEFT); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($admin['name']); ?></strong>
                                        <?php if ($admin['role'] === 'super_admin'): ?>
                                            <i class="bi bi-shield-fill-check text-danger ms-1" title="Super Admin"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['phone_number']); ?></td>
                                    <td>
                                        <?php if ($admin['role'] === 'super_admin'): ?>
                                            <span class="badge" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
                                                <i class="bi bi-shield-fill-check"></i> Super Admin
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background-color: #E57373; color: white;">
                                                SSC Admin
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($admin['is_active'] == 1): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle"></i> Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($admin['role'] === 'super_admin'): ?>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick='editAdmin(<?php echo htmlspecialchars(json_encode($admin), ENT_QUOTES, 'UTF-8'); ?>)'>
                                                <i class="bi bi-pencil"></i> 
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="deleteAdmin(<?php echo $admin['admin_id']; ?>, '<?php echo htmlspecialchars($admin['name'], ENT_QUOTES); ?>')">
                                                <i class="bi bi-trash"></i> 
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Admin Modal -->
<div class="modal fade" id="createAdminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background-color: #dc3545; color: white;">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus"></i> Add New SSC Admin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="./process/process_profile.php" id="createAdminForm">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="role" value="ssc_admin">
                <div class="modal-body p-4">
                    <div id="createErrors" class="alert alert-danger d-none" role="alert"></div>
                    
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i> <strong>Important:</strong> SSC Admins must use BSU G-Suite account (@g.batstate-u.edu.ph)
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Admin's Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="create_name" name="name" required 
                               minlength="2" maxlength="100" placeholder="Enter name">
                        <small class="text-muted">Minimum 2 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">G-Suite Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control form-control-lg" id="create_email" name="email" required
                               placeholder="23-12345@g.batstate-u.edu.ph">
                        <small class="text-muted">Must end with @g.batstate-u.edu.ph</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control form-control-lg" id="create_phone" name="phone" placeholder="09123456789" required 
                               pattern="^(09|\+639)\d{9}$">
                        <small class="text-muted">Format: 09123456789 or +639123456789</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control form-control-lg" id="create_password" name="password" required 
                               minlength="6" maxlength="50" placeholder="Enter password">
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Admin Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background-color: #ffc107; color: #000;">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil"></i> Edit Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="./process/process_profile.php" id="editAdminForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="role" value="ssc_admin">
                <input type="hidden" id="edit_admin_id" name="admin_id">
                <div class="modal-body p-4">
                    <div id="editErrors" class="alert alert-danger d-none" role="alert"></div>
                    
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i> <strong>Important:</strong> SSC Admins must use BSU G-Suite account (@g.batstate-u.edu.ph)
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="edit_name" name="name" required 
                               minlength="2" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">G-Suite Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control form-control-lg" id="edit_email" name="email" required>
                        <small class="text-muted">Must end with @g.batstate-u.edu.ph</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control form-control-lg" id="edit_phone" name="phone" required 
                               pattern="^(09|\+639)\d{9}$">
                        <small class="text-muted">Format: 09123456789 or +639123456789</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" id="edit_is_active" name="is_active" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <small class="text-muted">Inactive admins cannot access the system</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="js/profile.js"></script>