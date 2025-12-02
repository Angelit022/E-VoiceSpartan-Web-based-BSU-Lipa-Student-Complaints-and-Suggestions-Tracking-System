<?php
require_once '../db.php';
?>

<div class="container-fluid" style="max-width: 900px; margin: 0 auto;">
    <div class="mb-5">
        <h2 class="fw-bold text-danger"><i class="bi bi-person-circle"></i> Profile</h2>
        <p class="text-muted">Manage system account details</p>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person-circle me-2"></i> My Profile</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small">Name</label>
                        <input type="text" class="form-control form-control-lg" value="<?php echo htmlspecialchars($admin_name); ?>" disabled>
                        <small class="text-muted d-block mt-1">Your display name in the system</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small">Email Address</label>
                        <div class="input-group">
                            <input type="email" class="form-control form-control-lg" value="<?php echo htmlspecialchars($admin_email); ?>" disabled>
                            <span class="input-group-text bg-light border">
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </span>
                        </div>
                        <small class="text-muted d-block mt-1">Your primary email address</small>
                    </div>
                    
                    <div class="mb-0">
                        <label class="form-label fw-semibold text-muted small">Account Role</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg" value="<?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $admin_role))); ?>" disabled>
                            <span class="input-group-text bg-danger text-white border-0">
                                <i class="bi bi-shield-check"></i>
                            </span>
                        </div>
                        <small class="text-muted d-block mt-1">Your permission level in the system</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100 bg-light">
                <div class="card-body d-flex flex-column justify-content-center h-100 p-4">
                    <div class="text-center mb-4">
                        <div class="bg-success bg-opacity-10 d-inline-flex rounded-circle justify-content-center align-items-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Account Active</h5>
                        <p class="text-muted small mb-0">Your account is active and fully operational</p>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="text-center">
                        <small class="text-muted d-block mb-3">
                            <i class="bi bi-clock-history me-1"></i>
                            Last login: <strong>Today at <?php echo date('h:i A'); ?></strong>
                        </small>
                        <small class="text-muted d-block">
                            <i class="bi bi-geo-alt me-1"></i>
                            Location: <strong>Philippines</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
</div>

<style>
.form-control:disabled, 
.form-select:disabled, 
.btn:disabled {
    cursor: not-allowed;
    opacity: 0.7;
}

.input-group-text {
    background-color: #f8f9fa !important;
}

.bg-success.bg-opacity-10 {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-success.bg-opacity-10 i {
    line-height: 1;
}
</style>