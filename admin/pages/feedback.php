<?php
require_once '../db.php';
require_once __DIR__ . '/../classes/FeedbackService.php';

$feedbackService = new FeedbackService();
$feedbacks = $feedbackService->getAllFeedback();
?>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="bi bi-star"></i> User Satisfaction Feedback</h2>
        <p class="text-muted">View feedback from students about their experience</p>
    </div>
    
    <?php if (empty($feedbacks)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No feedback received yet</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($feedbacks as $feedback): ?>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0">
                                    <?php echo htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']); ?>
                                </h6>
                                <span class="badge bg-<?php echo $feedback['type'] === 'Complaint' ? 'danger' : 'success'; ?>">
                                    <?php echo $feedback['type']; ?>
                                </span>
                            </div>
                            
                            <div class="mb-2">
                                <span class="text-warning">
                                    <?php for ($i = 0; $i < $feedback['rating']; $i++): ?>
                                        <i class="bi bi-star-fill"></i>
                                    <?php endfor; ?>
                                    <?php for ($i = $feedback['rating']; $i < 5; $i++): ?>
                                        <i class="bi bi-star"></i>
                                    <?php endfor; ?>
                                </span>
                                <small class="text-muted"><?php echo $feedback['rating']; ?>/5</small>
                            </div>
                            
                            <p class="mb-2"><?php echo htmlspecialchars($feedback['comments']); ?></p>
                            
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> <?php echo date('M d, Y H:i', strtotime($feedback['date_given'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>