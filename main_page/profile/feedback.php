<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    exit('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Unauthorized</div>');
}

require_once '../../db.php';
require_once '../classes/UserProfile.php';

$database = new Database();
$db = $database->getConnection();
$userProfile = new UserProfile($db, $_SESSION['user_id']);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

if (empty($id) || empty($type)) {
    http_response_code(400);
    exit('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Invalid parameters</div>');
}

// Check feedback status
$statusResult = $userProfile->checkFeedbackStatus($type, $id);

if (!$statusResult['success']) {
    http_response_code(403);
    exit('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ' . htmlspecialchars($statusResult['message']) . '</div>');
}

// Get submission details
$submission = $userProfile->getSubmissionDetails(ucfirst($type), $id);

$hasFeedback = $statusResult['has_feedback'];
$existingRating = $hasFeedback ? $statusResult['rating'] : 0;

$ratingLabels = [
    1 => ['text' => 'Poor', 'class' => 'poor'],
    2 => ['text' => 'Fair', 'class' => 'fair'],
    3 => ['text' => 'Good', 'class' => 'good'],
    4 => ['text' => 'Very Good', 'class' => 'very-good'],
    5 => ['text' => 'Excellent', 'class' => 'excellent']
];
?>

<div class="feedback-viewer-header">
    <h2>
        <i class="bi bi-star-fill"></i>
        Quick Review
    </h2>
    <p><?php echo $hasFeedback ? 'Your feedback has been recorded' : 'Help us improve by rating your experience'; ?></p>
    <div class="feedback-viewer-close" onclick="closeFeedbackModal()" title="Close (ESC)">
        <i class="bi bi-x"></i>
    </div>
</div>

<div class="feedback-viewer-body">
    <div class="feedback-submission-info">
        <h4>
            <i class="bi <?php echo $type === 'complaint' ? 'bi-exclamation-circle' : 'bi-lightbulb'; ?>"></i>
            <?php echo htmlspecialchars($submission['title']); ?>
        </h4>
        <p>
            <span class="badge" style="background: <?php echo $type === 'complaint' ? '#ff8c42' : '#4a90e2'; ?>; color: white;">
                <?php echo ucfirst($type); ?>
            </span>
            <span class="ms-2">#<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?></span>
        </p>
    </div>

    <?php if ($hasFeedback): ?>
        <div class="feedback-already-submitted">
            <i class="bi bi-check-circle-fill"></i>
            <h3>Thank You!</h3>
            <p>You have already submitted a review for this submission.</p>
            <div class="submitted-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi bi-star-fill" style="color: <?php echo $i <= $existingRating ? '#fbbf24' : '#e5e7eb'; ?>"></i>
                <?php endfor; ?>
                <span class="ms-2"><?php echo $ratingLabels[$existingRating]['text']; ?></span>
            </div>
        </div>
    <?php else: ?>
        <div class="star-rating-container">
            <h3>Rate Your Experience</h3>
            <p class="subtitle">Click on the stars to rate</p>
            
            <div class="star-rating" id="starRating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi bi-star-fill star" data-rating="<?php echo $i; ?>"></i>
                <?php endfor; ?>
            </div>

            <div class="rating-labels" id="ratingLabels">
                <?php foreach ($ratingLabels as $rating => $label): ?>
                    <div class="rating-label <?php echo $label['class']; ?>" data-label="<?php echo $rating; ?>">
                        <?php echo $label['text']; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="feedback-submit-btn" id="submitFeedbackBtn" data-id="<?php echo $id; ?>" data-type="<?php echo $type; ?>" disabled>
                <i class="bi bi-send-fill"></i>
                <span>Submit Review</span>
            </button>
        </div>
    <?php endif; ?>
</div>