<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Unauthorized access</div>';
    exit();
}

require_once '../../db.php';

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    http_response_code(400);
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Missing required parameters</div>';
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = intval($_GET['id']);
$type = $_GET['type'];

// Fetch submission details
if ($type === 'Complaint') {
    $query = "SELECT c.complaint_id as id, 'Complaint' as type, c.category, c.title, 
                     c.description, c.priority, s.status_name as status, c.date_submitted, c.is_anonymous
              FROM complaint c
              LEFT JOIN status s ON c.status_id = s.status_id
              WHERE c.complaint_id = ?";
} else {
    $query = "SELECT s.suggestion_id as id, 'Suggestion' as type, s.category, s.title, 
                     s.description, NULL as priority, st.status_name as status, s.date_submitted, s.is_anonymous
              FROM suggestion s
              LEFT JOIN status st ON s.status_id = st.status_id
              WHERE s.suggestion_id = ?";
}

$stmt = $db->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$submission = $result->fetch_assoc();
$stmt->close();

if (!$submission) {
    http_response_code(404);
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Submission not found</div>';
    exit();
}

$attachments = [];
if ($type === 'Complaint') {
    $attach_query = "SELECT attachment_id, file_path, file_type FROM attachment WHERE complaint_id = ? ORDER BY uploaded_at DESC";
    $attach_stmt = $db->prepare($attach_query);
    $attach_stmt->bind_param("i", $id);
    $attach_stmt->execute();
    $attach_result = $attach_stmt->get_result();
    while ($row = $attach_result->fetch_assoc()) {
        $attachments[] = $row;
    }
    $attach_stmt->close();
}
?>

<div class="submission-details">
    <div class="mb-3">
        <h6 class="text-muted mb-2"><i class="bi bi-info-circle"></i> Submission Type</h6>
        <p class="mb-0">
            <span class="badge <?php echo $type === 'Complaint' ? 'bg-danger' : 'bg-warning'; ?>">
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

    <?php if ($type === 'Complaint' && $submission['priority']): ?>
    <div class="mb-3">
        <h6 class="text-muted mb-2"><i class="bi bi-exclamation-triangle"></i> Priority</h6>
        <p class="mb-0">
            <span class="badge <?php 
                echo $submission['priority'] === 'High' ? 'bg-danger' : 
                     ($submission['priority'] === 'Medium' ? 'bg-warning text-dark' : 'bg-info'); 
            ?>">
                <?php echo htmlspecialchars($submission['priority']); ?>
            </span>
        </p>
    </div>
    <?php endif; ?>


    <div class="mb-3">
        <h6 class="text-muted mb-2"><i class="bi bi-file-earmark-text"></i> Description</h6>
        <p class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($submission['description']); ?></p>
    </div>

    <!-- Display attachments section -->
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
            <span class="badge <?php 
                echo match(strtolower(str_replace(' ', '-', $submission['status']))) {
                    'pending' => 'bg-warning text-dark',
                    'in-progress' => 'bg-info',
                    'resolved' => 'bg-success',
                    'rejected' => 'bg-danger',
                    default => 'bg-secondary'
                };
            ?>">
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
