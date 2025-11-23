<?php
require_once '../db.php';
require_once __DIR__ . '/../classes/DashboardService.php';

// No activity logging for page views

$dashboardService = new DashboardService();
$stats = $dashboardService->getStatistics();
$recent_submissions = $dashboardService->getRecentSubmissions(5);
$analytics = $dashboardService->getAnalyticsData();
?>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="bi bi-graph-up-arrow"></i> Dashboard Overview</h2>
        <p class="text-muted">Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</p>
    </div>
    
    <!-- Statistics Cards with Bootstrap responsive breakpoints -->
    <div class="row g-2 g-sm-2 g-md-3 mb-4">
        <div class="col-6 col-sm-4 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #FF8C00;">
                <div class="card-body p-2 p-sm-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Complaints</h6>
                            <h3 class="mb-0 fw-bold" style="color: #FF8C00;"><?php echo $stats['total_complaints']; ?></h3>
                        </div>
                        <i class="bi bi-exclamation-circle" style="font-size: 1.75rem; opacity: 0.2; color: #FF8C00;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-sm-4 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd;">
                <div class="card-body p-2 p-sm-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Suggestions</h6>
                            <h3 class="mb-0 fw-bold" style="color: #0d6efd;"><?php echo $stats['total_suggestions']; ?></h3>
                        </div>
                        <i class="bi bi-lightbulb" style="font-size: 1.75rem; opacity: 0.2; color: #0d6efd;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #FFEB3B;">
                <div class="card-body p-2 p-sm-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Pending</h6>
                            <h3 class="mb-0 fw-bold" style="color: #F9A825;"><?php echo $stats['pending_complaints']; ?></h3>
                        </div>
                        <i class="bi bi-hourglass-split" style="font-size: 1.75rem; opacity: 0.2; color: #F9A825;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-sm-4 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #64B5F6;">
                <div class="card-body p-2 p-sm-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.75rem;">In Progress</h6>
                            <h3 class="mb-0 fw-bold" style="color: #1976D2;"><?php echo $stats['in_progress_complaints']; ?></h3>
                        </div>
                        <i class="bi bi-arrow-repeat" style="font-size: 1.75rem; opacity: 0.2; color: #1976D2;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-sm-4 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #81C784;">
                <div class="card-body p-2 p-sm-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Resolved</h6>
                            <h3 class="mb-0 fw-bold" style="color: #388E3C;"><?php echo $stats['resolved_complaints']; ?></h3>
                        </div>
                        <i class="bi bi-check-circle" style="font-size: 1.75rem; opacity: 0.2; color: #388E3C;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-sm-4 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #E57373;">
                <div class="card-body p-2 p-sm-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Rejected</h6>
                            <h3 class="mb-0 fw-bold" style="color: #D32F2F;"><?php echo $stats['rejected_complaints']; ?></h3>
                        </div>
                        <i class="bi bi-x-circle" style="font-size: 1.75rem; opacity: 0.2; color: #D32F2F;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Submissions -->
    <div class="card border-0 shadow-sm mb-4">
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
                                    <td><small class="text-muted fw-semibold">#<?php echo str_pad($submission['id'], 5, '0', STR_PAD_LEFT); ?></small></td>
                                    <td>
                                        <?php if ($submission['is_anonymous']): ?>
                                            <i class="bi bi-incognito me-1"></i> <strong>Anonymous</strong>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($submission['title'], 0, 40)); ?></td>
                                    <td>
                                        <span class="badge" style="background-color: <?php echo $submission['type'] === 'Complaint' ? '#FF8C00' : '#0d6efd'; ?>;">
                                            <?php echo $submission['type']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: <?php echo DashboardService::getStatusBadgeColor($submission['status_name']); ?>;">
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

<!-- Updated Analytics Section with new layout -->
<div class="row g-3 mb-4">
    <!-- Row 1: Monthly Trend (Left) + Status Charts (Right - Stacked) -->
    
    <!-- Monthly Trend -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">Monthly Trend</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Last 5 Months
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item trend-filter" data-months="3">Last 3 Months</a></li>
                            <li><a class="dropdown-item trend-filter" data-months="5">Last 5 Months</a></li>
                            <li><a class="dropdown-item trend-filter" data-months="6">Last 6 Months</a></li>
                            <li><a class="dropdown-item trend-filter" data-months="12">Last 12 Months</a></li>
                        </ul>
                    </div>
                </div>
                <div style="padding-bottom: 10px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Complaints & Suggestions by Status (Stacked Right) -->
    <div class="col-12 col-lg-6">
        <div class="row g-3">
            <!-- Complaints by Status -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Complaints by Status</h6>
                        <div class="status-chart-container">
                            <canvas id="complaintStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Suggestions by Status -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Suggestions by Status</h6>
                        <div class="status-chart-container">
                            <canvas id="suggestionStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Categories (Side by Side) -->
<div class="row g-3 mb-4">
    <!-- Complaints by Category -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3 fw-bold">Complaints by Category</h6>
                <div class="doughnut-wrapper">
                    <canvas id="catChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Suggestions by Category -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3 fw-bold">Suggestions by Category</h6>
                <div class="doughnut-wrapper">
                    <canvas id="suggestionCatChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isSuperAdmin()): ?>
<!-- Quick Link to Activity Logs (Super Admin Only) -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1 fw-bold"><i class="bi bi-activity me-2"></i>Activity Monitoring</h6>
                <p class="text-muted mb-0 small">Track system activities and user actions</p>
            </div>
            <a href="?page=activity_log" class="btn btn-danger">
                <i class="bi bi-arrow-right me-1"></i> View Activity Logs
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

</div>

<script>
const phpData = {
    months: <?php echo json_encode($analytics['months']); ?>,
    complaintTrend: <?php echo json_encode($analytics['complaint_trend']); ?>,
    suggestionTrend: <?php echo json_encode($analytics['suggestion_trend']); ?>,
    categories: <?php echo json_encode(array_keys($analytics['complaint_categories'])); ?>,
    categoryCounts: <?php echo json_encode(array_values($analytics['complaint_categories'])); ?>,
    categoryColors: ["#b22222", "#8b0000", "#d32f2f", "#9e9e9e", "#757575", "#dc3545", "#6c757d", "#b0b0b0"],
    suggestionCategories: <?php echo json_encode(array_keys($analytics['suggestion_categories'])); ?>,
    suggestionCategoryCounts: <?php echo json_encode(array_values($analytics['suggestion_categories'])); ?>,
    suggestionCategoryColors: ["#b22222", "#8b0000", "#d32f2f", "#9e9e9e", "#757575", "#dc3545", "#6c757d", "#b0b0b0"],
    complaintStatuses: <?php echo json_encode(array_values($analytics['complaint_statuses'])); ?>,
    complaintStatusLabels: <?php echo json_encode(array_keys($analytics['complaint_statuses'])); ?>,
    suggestionStatuses: <?php echo json_encode(array_values($analytics['suggestion_statuses'])); ?>,
    suggestionStatusLabels: <?php echo json_encode(array_keys($analytics['suggestion_statuses'])); ?>
};
</script>