<?php
require_once '../db.php';
require_once __DIR__ . '/../classes/ActivityLogService.php';
require_once __DIR__ . '/../classes/AdminActivityLog.php';

// Initialize activity logger
$adminId = $_SESSION['admin_id'] ?? null;
$adminName = $_SESSION['first_name'] ?? 'Admin';

if ($adminId) {
    $activityLog = new AdminActivityLog($adminId, $adminName);
    
    // Only log export action, not the page view
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        $activityLog->logActivityLogsExport('CSV');
    }
}

$activityLogService = new ActivityLogService();

// Get filter parameters
$user_filter = $_GET['user_filter'] ?? '';
$type_filter = $_GET['type_filter'] ?? '';
$activity_filter = $_GET['activity_filter'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$sort_by = $_GET['sort_by'] ?? 'created_at';
$sort_order = $_GET['sort_order'] ?? 'DESC';

// Get activity logs with filters
$activity_logs = $activityLogService->getActivityLogs([
    'user_filter' => $user_filter,
    'type_filter' => $type_filter,
    'activity_filter' => $activity_filter,
    'date_from' => $date_from,
    'date_to' => $date_to,
    'limit' => $limit,
    'sort_by' => $sort_by,
    'sort_order' => $sort_order
]);

// Get statistics
$stats = $activityLogService->getActivityStats();
?>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="bi bi-activity"></i> Activity Logs</h2>
        <p class="text-muted">Monitor all system activities and user actions</p>
    </div>

    <!-- Statistics Cards - Enhanced with Colors -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 stat-card-total">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-white mb-1 opacity-75" style="font-size: 0.85rem;">Total Activities</h6>
                            <h3 class="mb-0 fw-bold text-white"><?php echo $stats['total']; ?></h3>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="bi bi-list-ul"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 stat-card-students">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-white mb-1 opacity-75" style="font-size: 0.85rem;">Student Activities</h6>
                            <h3 class="mb-0 fw-bold text-white"><?php echo $stats['students']; ?></h3>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 stat-card-admins">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-white mb-1 opacity-75" style="font-size: 0.85rem;">Admin Activities</h6>
                            <h3 class="mb-0 fw-bold text-white"><?php echo $stats['admins']; ?></h3>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 stat-card-today">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-white mb-1 opacity-75" style="font-size: 0.85rem;">Today's Activities</h6>
                            <h3 class="mb-0 fw-bold text-white"><?php echo $stats['today']; ?></h3>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-funnel me-2"></i>Filters</h6>
                <button class="btn btn-sm btn-outline-secondary" id="clearFilters">
                    <i class="bi bi-x-circle me-1"></i>Clear Filters
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="filterForm" method="GET" action="">
                <input type="hidden" name="page" value="activity_log">
                <input type="hidden" name="sort_by" id="sortBy" value="<?php echo htmlspecialchars($sort_by); ?>">
                <input type="hidden" name="sort_order" id="sortOrder" value="<?php echo htmlspecialchars($sort_order); ?>">
                <div class="row g-3">
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label small">User ID/Email</label>
                        <input type="text" class="form-control" name="user_filter" id="userFilter" 
                               placeholder="Search by user ID or email" 
                               value="<?php echo htmlspecialchars($user_filter); ?>">
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small">User Type</label>
                        <select class="form-select" name="type_filter" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="student" <?php echo $type_filter === 'student' ? 'selected' : ''; ?>>Student</option>
                            <option value="admin" <?php echo $type_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small">Activity Type</label>
                        <select class="form-select" name="activity_filter" id="activityFilter">
                            <option value="">All Activities</option>
                            <option value="login" <?php echo $activity_filter === 'login' ? 'selected' : ''; ?>>Login</option>
                            <option value="logout" <?php echo $activity_filter === 'logout' ? 'selected' : ''; ?>>Logout</option>
                            <option value="create" <?php echo $activity_filter === 'create' ? 'selected' : ''; ?>>Create</option>
                            <option value="edit" <?php echo $activity_filter === 'edit' ? 'selected' : ''; ?>>Edit</option>
                            <option value="delete" <?php echo $activity_filter === 'delete' ? 'selected' : ''; ?>>Delete</option>
                            <option value="view" <?php echo $activity_filter === 'view' ? 'selected' : ''; ?>>View</option>
                            <option value="other" <?php echo $activity_filter === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small">Date From</label>
                        <input type="date" class="form-control" name="date_from" id="dateFrom" 
                               value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label small">Date To</label>
                        <input type="date" class="form-control" name="date_to" id="dateTo" 
                               value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-1">
                        <label class="form-label small">Limit</label>
                        <select class="form-select" name="limit" id="limitSelect">
                            <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100</option>
                            <option value="200" <?php echo $limit === 200 ? 'selected' : ''; ?>>200</option>
                            <option value="500" <?php echo $limit === 500 ? 'selected' : ''; ?>>500</option>
                        </select>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-12 d-flex align-items-end">
                        <button type="submit" class="btn btn-danger me-2">
                            <i class="bi bi-search me-1"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2"></i>Activity Logs</h6>
                <button class="btn btn-sm btn-outline-danger" id="exportBtn">
                    <i class="bi bi-download me-1"></i>Export
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-hover table-striped mb-0" id="activityTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="width: 8%;">Log ID</th>
                            <th class="sortable" style="width: 12%; cursor: pointer;" data-sort="created_at">
                                Date & Time
                                <i class="bi bi-arrow-down-up ms-1 sort-icon"></i>
                            </th>
                            <th style="width: 13%;">User ID and Email</th>
                            <th class="text-center sortable" style="width: 8%; cursor: pointer;" data-sort="user_type">
                                User Type
                                <i class="bi bi-arrow-down-up ms-1 sort-icon"></i>
                            </th>
                            <th class="text-center sortable" style="width: 10%; cursor: pointer;" data-sort="activity_type">
                                Activity Type
                                <i class="bi bi-arrow-down-up ms-1 sort-icon"></i>
                            </th>
                            <th style="width: 25%;">Description</th>
                            <th style="width: 12%;">IP Address</th>
                            <th style="width: 12%;">User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($activity_logs)): ?>
                            <?php foreach ($activity_logs as $log): ?>
                                <tr data-log-id="<?php echo htmlspecialchars($log['log_id']); ?>">
                                    <td>
                                        <span class="badge bg-info text-white">
                                            <?php echo htmlspecialchars($log['log_id']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="d-block"><?php echo date('M d, Y', strtotime($log['created_at'])); ?></small>
                                        <small class="text-muted"><?php echo date('h:i:s A', strtotime($log['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo htmlspecialchars($log['user_id']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($log['user_type'] === 'student'): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-person"></i> Student
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-shield-check"></i> Admin
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $activity_badges = [
                                            'login' => ['bg-primary', 'bi-box-arrow-in-right'],
                                            'logout' => ['bg-secondary', 'bi-box-arrow-right'],
                                            'create' => ['bg-success', 'bi-plus-circle'],
                                            'edit' => ['bg-warning', 'bi-pencil'],
                                            'delete' => ['bg-danger', 'bi-trash'],
                                            'view' => ['bg-info', 'bi-eye'],
                                            'other' => ['bg-dark', 'bi-three-dots']
                                        ];
                                        $badge_info = $activity_badges[$log['activity_type']] ?? $activity_badges['other'];
                                        ?>
                                        <span class="badge <?php echo $badge_info[0]; ?>">
                                            <i class="bi <?php echo $badge_info[1]; ?>"></i> 
                                            <?php echo ucfirst($log['activity_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($log['activity_description']); ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="bi bi-router me-1"></i>
                                            <?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted text-truncate d-block" style="max-width: 150px;" 
                                               title="<?php echo htmlspecialchars($log['user_agent'] ?? 'N/A'); ?>">
                                            <i class="bi bi-browser-chrome me-1"></i>
                                            <?php echo htmlspecialchars($log['user_agent'] ?? 'N/A'); ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3"></i>
                                    <p class="mt-2">No activity logs found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (!empty($activity_logs)): ?>
        <div class="card-footer bg-light text-center">
            <small class="text-muted">
                Showing <?php echo count($activity_logs); ?> of <?php echo $stats['total']; ?> total activities
            </small>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const activityLogsData = <?php echo json_encode($activity_logs); ?>;
const currentSortBy = '<?php echo $sort_by; ?>';
const currentSortOrder = '<?php echo $sort_order; ?>';
</script>