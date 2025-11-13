<?php
require_once '../db.php';
?>

<div class="container-fluid" style="max-width: 600px;">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="bi bi-gear"></i> System Settings</h2>
        <p class="text-muted">Manage system preferences and configurations</p>
    </div>
    
    <!-- Profile Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold"><i class="bi bi-person"></i> My Profile</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin_name); ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($admin_email); ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $admin_role))); ?>" disabled>
            </div>
        </div>
    </div>
    
    <!-- Preferences Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold"><i class="bi bi-toggles"></i> Preferences</h6>
        </div>
        <div class="card-body">
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="darkModeToggle">
                <label class="form-check-label" for="darkModeToggle">
                    Dark Mode (Coming Soon)
                </label>
            </div>
            <button class="btn btn-outline-danger" onclick="resetSettings()">
                <i class="bi bi-arrow-clockwise"></i> Reset to Default
            </button>
        </div>
    </div>
    

</div>