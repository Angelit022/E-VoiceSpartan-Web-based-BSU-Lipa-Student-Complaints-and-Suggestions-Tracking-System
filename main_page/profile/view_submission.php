<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Unauthorized access</div>';
    exit();
}

require_once '../../db.php';
require_once '../classes/UserProfile.php';
require_once '../classes/StudentActivityLog.php';

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    http_response_code(400);
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Missing required parameters</div>';
    exit();
}

$student_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();
$userProfile = new UserProfile($db, $student_id);

$id = intval($_GET['id']);
$type = $_GET['type'];

// Initialize activity logger
$activityLog = new StudentActivityLog($student_id);

// Fetch submission details using class method
$submission = $userProfile->getSubmissionDetails($type, $id);

if (!$submission) {
    http_response_code(404);
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Submission not found</div>';
    exit();
}

// Log submission view
$activityLog->logSubmissionView($type, $id);

// Get attachments if complaint
$attachments = [];
if ($type === 'Complaint') {
    $attachments = $userProfile->getAttachments($id);
}
?>

<div class="submission-details">
    <div class="mb-3">
        <h6 class="text-muted mb-2"><i class="bi bi-info-circle"></i> Submission Type</h6>
        <p class="mb-0">
            <span class="badge <?php echo $type === 'Complaint' ? 'bg-complaint' : 'bg-warning'; ?>">
                <i class="bi <?php echo $type === 'Complaint' ? 'bi-exclamation-circle' : 'bi-lightbulb'; ?>"></i>
                <?php echo htmlspecialchars($type); ?>
            </span>
            <?php if (!empty($submission['is_anonymous']) && intval($submission['is_anonymous']) === 1): ?>
                <span class="badge bg-secondary ms-1" title="Submitted anonymously"><i class="bi bi-shield-lock"></i> Anonymous</span>
            <?php endif; ?>
        </p>
    </div>

    <div class="mb-3">
        <h6 class="text-muted mb-2"><i class="bi bi-hash"></i> Reference ID</h6>
        <p class="mb-0 fw-bold">#<?php echo str_pad($submission['id'], 5, '0', STR_PAD_LEFT); ?></p>
    </div>

    <div class="mb-3">
        <h6 class="text-muted mb-2"><i class="bi bi-file-text"></i> Title</h6>
        <p class="mb-0 fw-bold"><?php echo htmlspecialchars($submission['title']); ?></p>
    </div>

    <div class="mb-3">
        <h6 class="text-muted mb-2"><i class="bi bi-folder"></i> Category</h6>
        <p class="mb-0"><?php echo htmlspecialchars($submission['category'] ?? 'N/A'); ?></p>
    </div>

    <?php if (!empty($submission['priority'])): ?>
    <div class="mb-3">
        <h6 class="text-muted mb-2"><i class="bi bi-exclamation-triangle"></i> Priority</h6>
        <p class="mb-0">
            <span class="badge <?php echo UserProfile::getPriorityBadgeClass($submission['priority']); ?>">
                <?php echo htmlspecialchars($submission['priority']); ?>
            </span>
        </p>
    </div>
    <?php endif; ?>

    <div class="mb-3">
        <h6 class="text-muted mb-2"><i class="bi bi-file-earmark-text"></i> Description</h6>
        <p class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($submission['description']); ?></p>
    </div>

    <?php if (!empty($attachments)): ?>
    <div class="mb-3">
        <h6 class="text-muted mb-2"><i class="bi bi-paperclip"></i> Attachments</h6>
        <div class="list-group list-group-flush">
            <?php foreach ($attachments as $attachment): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi <?php
                            $ext = pathinfo($attachment['file_path'], PATHINFO_EXTENSION);
                            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) echo 'bi-image';
                            elseif (strtolower($ext) === 'pdf') echo 'bi-file-pdf';
                            elseif (in_array(strtolower($ext), ['doc', 'docx'])) echo 'bi-file-word';
                            elseif (strtolower($ext) === 'mp4') echo 'bi-film';
                            else echo 'bi-file-earmark';
                        ?> me-2"></i>
                        <a href="../../<?php echo htmlspecialchars($attachment['file_path']); ?>" target="_blank" class="text-decoration-none">
                            <?php echo htmlspecialchars(basename($attachment['file_path'])); ?>
                        </a>
                    </div>
                    <span class="badge bg-secondary"><?php echo strtoupper(pathinfo($attachment['file_path'], PATHINFO_EXTENSION)); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="mb-3">
        <h6 class="text-muted mb-2"><i class="bi bi-badge"></i> Status</h6>
        <p class="mb-0">
            <span class="badge <?php echo UserProfile::getStatusBadgeClass($submission['status']); ?>">
                <i class="bi <?php 
                    echo match(strtolower(str_replace(' ', '-', $submission['status']))) {
                        'pending' => 'bi-hourglass-split',
                        'in-progress' => 'bi-arrow-repeat',
                        'resolved' => 'bi-check-circle',
                        'rejected' => 'bi-x-circle',
                        default => 'bi-question-circle'
                    };
                ?>"></i>
                <?php echo htmlspecialchars($submission['status']); ?>
            </span>
        </p>
    </div>

    <div class="mb-0">
        <h6 class="text-muted mb-2"><i class="bi bi-calendar-event"></i> Date Submitted</h6>
        <p class="mb-0"><?php echo date('F d, Y @ h:i A', strtotime($submission['date_submitted'])); ?></p>
    </div>
</div>

<style>
/* Orange badge for Complaint type in view modal */
.badge.bg-complaint {
    background: linear-gradient(135deg, #ff8c42 0%, #ff6b35 100%) !important;
    color: white !important;
}
</style>