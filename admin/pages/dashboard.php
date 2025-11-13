<?php
require_once '../db.php';
require_once __DIR__ . '/../classes/DashboardService.php';

$dashboardService = new DashboardService();
$stats = $dashboardService->getStatistics();
$recent_submissions = $dashboardService->getRecentSubmissions(5);
?>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="bi bi-speedometer2"></i> Dashboard Overview</h2>
        <p class="text-muted">Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Total Complaints</h6>
                            <h3 class="mb-0 text-danger fw-bold"><?php echo $stats['total_complaints']; ?></h3>
                        </div>
                        <i class="bi bi-exclamation-circle text-danger" style="font-size: 2.5rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Total Suggestions</h6>
                            <h3 class="mb-0 text-primary fw-bold"><?php echo $stats['total_suggestions']; ?></h3>
                        </div>
                        <i class="bi bi-lightbulb text-primary" style="font-size: 2.5rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h3 class="mb-0 text-warning fw-bold"><?php echo $stats['pending_complaints']; ?></h3>
                        </div>
                        <i class="bi bi-hourglass-split text-warning" style="font-size: 2.5rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0dcaf0;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">In Progress</h6>
                            <h3 class="mb-0 text-info fw-bold"><?php echo $stats['in_progress_complaints']; ?></h3>
                        </div>
                        <i class="bi bi-arrow-repeat text-info" style="font-size: 2.5rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Resolved</h6>
                            <h3 class="mb-0 text-success fw-bold"><?php echo $stats['resolved_complaints']; ?></h3>
                        </div>
                        <i class="bi bi-check-circle text-success" style="font-size: 2.5rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6c757d;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Rejected</h6>
                            <h3 class="mb-0 text-secondary fw-bold"><?php echo $stats['rejected_complaints']; ?></h3>
                        </div>
                        <i class="bi bi-x-circle text-secondary" style="font-size: 2.5rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Submissions -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i> Recent Submissions</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Student Name</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_submissions)): ?>
                            <?php foreach ($recent_submissions as $submission): ?>
                                <tr>
                                    <td><small class="text-muted">#<?php echo $submission['id']; ?></small></td>
                                    <td>
                                        <?php if ($submission['is_anonymous']): ?>
                                            <i class="bi bi-incognito me-1"></i> <strong>Anonymous</strong>
                                            <br>
                                            <small class="badge bg-info">Anonymous</small>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($submission['title'], 0, 40)); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $submission['type'] === 'Complaint' ? 'danger' : 'success'; ?>">
                                            <?php echo $submission['type']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo DashboardService::getStatusBadgeClass($submission['status_name']); ?>">
                                            <?php echo $submission['status_name']; ?>
                                        </span>
                                    </td>
                                    <td><small class="text-muted"><?php echo date('M d, Y', strtotime($submission['date_submitted'])); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox"></i> No submissions yet
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light text-center">
            <a href="?page=responses" class="btn btn-sm btn-danger">
                <i class="bi bi-arrow-right"></i> View All Reports
            </a>
        </div>
    </div>
</div>