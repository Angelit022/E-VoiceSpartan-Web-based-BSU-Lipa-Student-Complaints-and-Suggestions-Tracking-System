<?php
require_once '../db.php';
require_once __DIR__ . '/../classes/FeedbackService.php';

$feedbackService = new FeedbackService();
$feedbacks = $feedbackService->getAllFeedback();

$totalFeedbacks = count($feedbacks);
$avgRating = 0;
$ratingDistribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

if ($totalFeedbacks > 0) {
    $totalRating = 0;
    foreach ($feedbacks as $feedback) {
        $totalRating += $feedback['rating'];
        $ratingDistribution[$feedback['rating']]++;
    }
    $avgRating = round($totalRating / $totalFeedbacks, 1);
}

$ratingLabels = [
    1 => 'Poor',
    2 => 'Fair',
    3 => 'Good',
    4 => 'Very Good',
    5 => 'Excellent'
];
?>

<link rel="stylesheet" href="css/feedback.css">

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="bi bi-star-fill text-warning"></i> User Satisfaction Feedback</h2>
        <p class="text-muted">View ratings and feedback from students about their experience</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                                <i class="bi bi-chat-square-text fs-3"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Feedback</h6>
                            <h3 class="mb-0 fw-bold"><?php echo $totalFeedbacks; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="bi bi-graph-up-arrow fs-3"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Average Rating</h6>
                            <h3 class="mb-0 fw-bold">
                                <?php echo $avgRating; ?> 
                                <small class="text-warning fs-5">★</small>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-3 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Rating Distribution</h6>
                    <div class="row g-2">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-warning fw-bold" style="min-width: 30px;"><?php echo $i; ?>★</span>
                                    <div class="progress flex-grow-1" style="height: 20px;">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: <?php echo $totalFeedbacks > 0 ? ($ratingDistribution[$i] / $totalFeedbacks * 100) : 0; ?>%"
                                             aria-valuenow="<?php echo $ratingDistribution[$i]; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="<?php echo $totalFeedbacks; ?>">
                                        </div>
                                    </div>
                                    <span class="text-muted" style="min-width: 40px;"><?php echo $ratingDistribution[$i]; ?></span>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($feedbacks)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #e5e7eb;"></i>
                <h4 class="mt-3 mb-2 fw-bold text-muted">No Feedback Yet</h4>
                <p class="text-muted mb-0">Student feedback will appear here once they rate resolved submissions.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-list-ul me-2"></i>
                    All Feedback (<?php echo $totalFeedbacks; ?>)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="feedbackTable">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Student</th>
                                <th class="px-4 py-3">Submission</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Rating</th>
                                <th class="px-4 py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody id="feedbackTableBody">
                            <?php foreach ($feedbacks as $feedback): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px; font-weight: 700;">
                                                <?php 
                                                    $firstInitial = strtoupper(substr($feedback['first_name'], 0, 1));
                                                    $lastInitial = strtoupper(substr($feedback['last_name'], 0, 1));
                                                    echo $firstInitial . $lastInitial;
                                                ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold">
                                                    <?php echo htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']); ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($feedback['student_id']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($feedback['title']); ?>">
                                            <?php echo htmlspecialchars($feedback['title']); ?>
                                        </div>
                                        <small class="text-muted">
                                            #<?php echo str_pad($feedback['submission_id'], 5, '0', STR_PAD_LEFT); ?>
                                        </small>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge rounded-pill" 
                                              style="background: <?php echo $feedback['type'] === 'Complaint' ? 'linear-gradient(135deg, #ff8c42 0%, #ff6b35 100%)' : 'linear-gradient(135deg, #4a90e2 0%, #357abd 100%)'; ?>; 
                                                     color: white; 
                                                     padding: 0.5rem 1rem;">
                                            <i class="bi <?php echo $feedback['type'] === 'Complaint' ? 'bi-exclamation-circle' : 'bi-lightbulb'; ?> me-1"></i>
                                            <?php echo $feedback['type']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="d-flex flex-column gap-1">
                                            <div class="text-warning" style="font-size: 1.25rem;">
                                                <?php for ($i = 0; $i < $feedback['rating']; $i++): ?>
                                                    <i class="bi bi-star-fill"></i>
                                                <?php endfor; ?>
                                                <?php for ($i = $feedback['rating']; $i < 5; $i++): ?>
                                                    <i class="bi bi-star" style="opacity: 0.3;"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="fw-bold" 
                                                   style="color: <?php 
                                                       echo match($feedback['rating']) {
                                                           1 => '#ef4444',
                                                           2 => '#f59e0b',
                                                           3 => '#fbbf24',
                                                           4 => '#10b981',
                                                           5 => '#3b82f6',
                                                           default => '#6c757d'
                                                       };
                                                   ?>;">
                                                <?php echo $ratingLabels[$feedback['rating']]; ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?php echo date('M d, Y', strtotime($feedback['date_given'])); ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            <?php echo date('h:i A', strtotime($feedback['date_given'])); ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-light d-flex justify-content-between align-items-center py-3 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 fw-semibold">Rows per page:</label>
                    <select class="form-select form-select-sm" style="width: 70px; padding-right: 2rem;" id="feedbackRowsPerPage" onchange="changeFeedbackPagination()">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div>
                    <small class="text-muted">Showing <span id="feedbackStartRow">1</span> to <span id="feedbackEndRow">10</span> of <span id="feedbackTotalRows"><?php echo $totalFeedbacks; ?></span> entries</small>
                </div>
                <nav aria-label="Feedback pagination">
                    <ul class="pagination mb-0" id="feedbackPagination"></ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="js/feedback.js"></script>