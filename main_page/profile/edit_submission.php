<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

require_once '../../db.php';
require_once '../classes/UserProfile.php';
require_once '../classes/Attachment.php';

$user_id = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    exit(json_encode(['success' => false, 'message' => 'Database connection error']));
}

$userProfile = new UserProfile($conn, $user_id);

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (empty($type) || empty($id)) {
    exit(json_encode(['success' => false, 'message' => 'Invalid request']));
}

$result = $userProfile->getSubmissionForEdit($type, $id);

if (!$result['success']) {
    exit('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ' . htmlspecialchars($result['message']) . '</div>');
}

$submission = $result['data'];

$submission['id'] = $submission[$type === 'Complaint' ? 'complaint_id' : 'suggestion_id'] ?? $id;

$attachments = [];
if ($type === 'Complaint') {
    $attachments = $userProfile->getAttachments($id);
}

// Get categories based on type from database
$categories = [];
if ($type === 'Complaint') {
    $catQuery = "SHOW COLUMNS FROM complaint LIKE 'category'";
    $catResult = $conn->query($catQuery);
    if ($catResult && $catRow = $catResult->fetch_assoc()) {
        if (preg_match("/^enum\('(.*)'\)$/", $catRow['Type'], $matches)) {
            $enumString = $matches[1];
            $categories = explode("','", $enumString);
            $categories = array_map(function($cat) { 
                return trim($cat, " '\""); 
            }, $categories);
        }
    }
} else {
    $catQuery = "SHOW COLUMNS FROM suggestion LIKE 'category'";
    $catResult = $conn->query($catQuery);
    if ($catResult && $catRow = $catResult->fetch_assoc()) {
        if (preg_match("/^enum\('(.*)'\)$/", $catRow['Type'], $matches)) {
            $enumString = $matches[1];
            $categories = explode("','", $enumString);
            $categories = array_map(function($cat) { 
                return trim($cat, " '\""); 
            }, $categories);
        }
    }
}

$currentCategory = $submission['category'] ?? '';
$isAnonymous = 0;
if (isset($submission['is_anonymous'])) {
    $isAnonymous = intval($submission['is_anonymous']);
}
?>

<form id="editForm" data-submission-id="<?php echo htmlspecialchars($submission['id'] ?? $id); ?>" data-submission-type="<?php echo htmlspecialchars($type); ?>">
    <div class="mb-3">
        <label class="form-label"><i class="bi bi-tag"></i> Type</label>
        <span class="badge <?php echo $type === 'Complaint' ? 'bg-danger' : 'bg-warning'; ?>">
            <i class="bi <?php echo $type === 'Complaint' ? 'bi-exclamation-circle' : 'bi-lightbulb'; ?>"></i>
            <?php echo htmlspecialchars($type); ?>
        </span>
    </div>

    <div class="mb-3">
        <label class="form-label"><i class="bi bi-hash"></i> Reference ID</label>
        <p class="form-control-plaintext">#<?php echo str_pad($submission['id'] ?? $id, 5, '0', STR_PAD_LEFT); ?></p>
    </div>

    <div class="mb-3">
        <label for="edit-title" class="form-label"><i class="bi bi-file-text"></i> Title <span class="text-danger">*</span></label>
        <input type="text" id="edit-title" name="title" class="form-control" value="<?php echo htmlspecialchars($submission['title'] ?? ''); ?>" required maxlength="255">
    </div>

    <div class="mb-3">
        <label for="edit-category" class="form-label"><i class="bi bi-folder"></i> Category <span class="text-danger">*</span></label>
        <select id="edit-category" name="category" class="form-select" required>
            <option value="">Select a category</option>
            <?php foreach ($categories as $cat): 
                $cleanCat = trim($cat);
                $isSelected = ($cleanCat === $currentCategory);
            ?>
                <option value="<?php echo htmlspecialchars($cleanCat); ?>" <?php echo $isSelected ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cleanCat); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="edit-priority" class="form-label"><i class="bi bi-exclamation-triangle"></i> Priority</label>
        <select id="edit-priority" name="priority" class="form-select">
            <option value="Low" <?php echo ($submission['priority'] ?? 'Medium') === 'Low' ? 'selected' : ''; ?>>Low</option>
            <option value="Medium" <?php echo ($submission['priority'] ?? 'Medium') === 'Medium' ? 'selected' : ''; ?>>Medium</option>
            <option value="High" <?php echo ($submission['priority'] ?? 'Medium') === 'High' ? 'selected' : ''; ?>>High</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="edit-description" class="form-label"><i class="bi bi-file-earmark-text"></i> Description <span class="text-danger">*</span></label>
        <textarea id="edit-description" name="description" class="form-control" rows="4" required minlength="10"><?php echo htmlspecialchars($submission['description'] ?? ''); ?></textarea>
    </div>

    <div class="mb-4">
        <div class="anonymous-toggle-container">
            <div class="anonymous-toggle-label">
                <i class="bi bi-incognito"></i>
                <span class="toggle-title">Submit Anonymously</span>
                <small class="text-muted d-block mt-1">Your identity will be hidden from public view</small>
            </div>
            <div class="toggle-switch-wrapper">
                <input type="checkbox" id="edit-anonymous" name="is_anonymous" class="toggle-switch-checkbox" <?php echo $isAnonymous === 1 ? 'checked' : ''; ?>>
                <label for="edit-anonymous" class="toggle-switch-label">
                    <span class="toggle-switch-inner"></span>
                    <span class="toggle-switch-switch"></span>
                </label>
            </div>
        </div>
    </div>

    <?php if ($type === 'Complaint'): ?>
        <div class="mb-3">
            <div class="attachments-header">
                <h6 class="attachments-title">
                    <i class="bi bi-paperclip"></i>
                    Attachments
                </h6>
                <button type="button" class="add-attachment-btn" id="addAttachmentBtn">
                    <i class="bi bi-plus-circle"></i>
                    Add File
                </button>
            </div>
            
            <input type="file" id="new-attachment" name="new_attachment" accept=".jpg,.jpeg,.png,.mp4,.pdf,.doc,.docx" style="display: none;">
            
            <div id="attachmentsList">
                <?php if (!empty($attachments)): ?>
                    <?php foreach ($attachments as $attachment): ?>
                        <div class="attachment-item" data-attachment-id="<?php echo $attachment['attachment_id']; ?>">
                            <div class="attachment-info">
                                <i class="bi <?php
                                    $ext = pathinfo($attachment['file_path'], PATHINFO_EXTENSION);
                                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) echo 'bi-image';
                                    elseif (strtolower($ext) === 'pdf') echo 'bi-file-pdf';
                                    elseif (in_array(strtolower($ext), ['doc', 'docx'])) echo 'bi-file-word';
                                    elseif (strtolower($ext) === 'mp4') echo 'bi-film';
                                    else echo 'bi-file-earmark';
                                ?>"></i>
                                <span class="attachment-name"><?php echo htmlspecialchars(basename($attachment['file_path'])); ?></span>
                            </div>
                            <button type="button" class="delete-attachment-btn btn-remove-file" title="Delete attachment">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-attachments" id="noAttachmentsMsg">
                        <i class="bi bi-inbox"></i> No attachments yet
                    </div>
                <?php endif; ?>
            </div>
            
            <small class="text-muted d-block mt-2">
                <i class="bi bi-info-circle"></i> Supported: Images (JPG, PNG), Videos (MP4), Documents (PDF, DOC) - Max 10MB, up to 5 files total
            </small>
        </div>
    <?php endif; ?>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-danger flex-grow-1">
            <i class="bi bi-check-circle"></i> Update
        </button>
        <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancel
        </button>
    </div>
</form>