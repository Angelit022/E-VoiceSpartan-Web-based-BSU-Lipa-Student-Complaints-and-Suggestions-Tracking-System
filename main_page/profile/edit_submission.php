<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

require_once '../../db.php';
require_once '../classes/Attachment.php';

$user_id = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    exit(json_encode(['success' => false, 'message' => 'Database connection error']));
}

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (empty($type) || empty($id)) {
    exit(json_encode(['success' => false, 'message' => 'Invalid request']));
}

// Fetch submission details
if ($type === 'Complaint') {
    $query = "SELECT c.complaint_id as id, 'Complaint' as type, c.category, c.title, 
                     c.description, c.priority, s.status_name as status, c.date_submitted, c.is_anonymous, c.student_id
              FROM complaint c
              LEFT JOIN status s ON c.status_id = s.status_id
              WHERE c.complaint_id = ?";
} else {
    $query = "SELECT s.suggestion_id as id, 'Suggestion' as type, s.category, s.title, 
                     s.description, NULL as priority, st.status_name as status, s.date_submitted, s.is_anonymous, s.student_id
              FROM suggestion s
              LEFT JOIN status st ON s.status_id = st.status_id
              WHERE s.suggestion_id = ?";
}

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$submission = $result->fetch_assoc();
$stmt->close();

if (!$submission) {
    exit(json_encode(['success' => false, 'message' => 'Submission not found']));
}

// Verify ownership
if ($submission['student_id'] !== $user_id) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

if ($submission['status'] !== 'Pending') {
    exit(json_encode(['success' => false, 'message' => 'This submission cannot be edited - only pending submissions can be modified']));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $priority = $_POST['priority'] ?? 'Medium';

    // Check if anything actually changed
    $hasChanges = false;
    
    if ($title !== $submission['title']) $hasChanges = true;
    if ($description !== $submission['description']) $hasChanges = true;
    if ($category !== $submission['category']) $hasChanges = true;
    if ($type === 'Complaint' && $priority !== $submission['priority']) $hasChanges = true;
    
    // Check if new file is uploaded
    $newFileUploaded = isset($_FILES['new_attachment']) && $_FILES['new_attachment']['error'] !== UPLOAD_ERR_NO_FILE;
    if ($newFileUploaded) $hasChanges = true;

    if (!$hasChanges) {
        echo json_encode(['success' => false, 'message' => 'No changes detected. Please modify at least one field before saving.']);
        exit;
    }

    // Validate inputs
    if (empty($title) || empty($description)) {
        echo json_encode(['success' => false, 'message' => 'Title and description are required']);
        exit;
    }

    if (mb_strlen($title) > 255) {
        echo json_encode(['success' => false, 'message' => 'Title must be 255 characters or less']);
        exit;
    }

    if (mb_strlen($description) < 10) {
        echo json_encode(['success' => false, 'message' => 'Description must be at least 10 characters']);
        exit;
    }

    // Update submission
    if ($type === 'Complaint') {
        $update_query = "UPDATE complaint SET title = ?, description = ?, category = ?, priority = ? WHERE complaint_id = ?";
    } else {
        $update_query = "UPDATE suggestion SET title = ?, description = ?, category = ? WHERE suggestion_id = ?";
    }

    $update_stmt = $conn->prepare($update_query);
    if ($type === 'Complaint') {
        $update_stmt->bind_param("ssssi", $title, $description, $category, $priority, $id);
    } else {
        $update_stmt->bind_param("sssi", $title, $description, $category, $id);
    }

    if ($update_stmt->execute()) {
        $update_stmt->close();
        
        // Handle new file upload for complaints
        $fileMessage = '';
        if ($type === 'Complaint' && $newFileUploaded) {
            $attachment = new Attachment($conn);
            $uploadResult = $attachment->saveAttachment($_FILES['new_attachment'], $id);
            
            if ($uploadResult['success']) {
                $fileMessage = ' File uploaded successfully.';
            } else {
                $fileMessage = ' Warning: ' . $uploadResult['message'];
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Submission updated successfully' . $fileMessage]);
        exit;
    } else {
        $update_stmt->close();
        echo json_encode(['success' => false, 'message' => 'Failed to update submission']);
        exit;
    }
}

$attachments = [];
if ($type === 'Complaint') {
    $attach_query = "SELECT attachment_id, file_path FROM attachment WHERE complaint_id = ? ORDER BY uploaded_at DESC";
    $attach_stmt = $conn->prepare($attach_query);
    $attach_stmt->bind_param("i", $id);
    $attach_stmt->execute();
    $attach_result = $attach_stmt->get_result();
    while ($row = $attach_result->fetch_assoc()) {
        $attachments[] = $row;
    }
    $attach_stmt->close();
}
?>

<style>
.attachment-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
    transition: all 0.2s;
}

.attachment-item:hover {
    background: #e9ecef;
}

.attachment-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
    min-width: 0;
}

.attachment-name {
    font-size: 0.875rem;
    color: #495057;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.delete-attachment-btn {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.delete-attachment-btn:hover {
    background: #c82333;
    transform: scale(1.1);
}

.add-attachment-btn {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 0.375rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.add-attachment-btn:hover {
    background: #c82333;
}

#new-attachment {
    display: none;
}

.attachments-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.attachments-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #212529;
    margin: 0;
}

.no-attachments {
    padding: 1rem;
    text-align: center;
    color: #6c757d;
    font-size: 0.875rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
}
</style>

<form id="editForm" onsubmit="submitEditForm(<?php echo $id; ?>, '<?php echo htmlspecialchars($type); ?>'); return false;">
    <div class="mb-3">
        <label class="form-label"><i class="bi bi-tag"></i> Type</label>
        <span class="badge <?php echo $type === 'Complaint' ? 'bg-danger' : 'bg-warning'; ?>">
            <i class="bi <?php echo $type === 'Complaint' ? 'bi-exclamation-circle' : 'bi-lightbulb'; ?>"></i>
            <?php echo htmlspecialchars($type); ?>
        </span>
    </div>

    <div class="mb-3">
        <label class="form-label"><i class="bi bi-hash"></i> Reference ID</label>
        <p class="form-control-plaintext">#<?php echo str_pad($submission['id'], 5, '0', STR_PAD_LEFT); ?></p>
    </div>

    <div class="mb-3">
        <label for="edit-title" class="form-label"><i class="bi bi-file-text"></i> Title <span class="text-danger">*</span></label>
        <input type="text" id="edit-title" name="title" class="form-control" value="<?php echo htmlspecialchars($submission['title']); ?>" required maxlength="255">
    </div>

    <div class="mb-3">
        <label for="edit-category" class="form-label"><i class="bi bi-folder"></i> Category</label>
        <input type="text" id="edit-category" name="category" class="form-control" value="<?php echo htmlspecialchars($submission['category'] ?? ''); ?>">
    </div>

    <div class="mb-3">
        <label for="edit-description" class="form-label"><i class="bi bi-file-earmark-text"></i> Description <span class="text-danger">*</span></label>
        <textarea id="edit-description" name="description" class="form-control" rows="4" required minlength="10"><?php echo htmlspecialchars($submission['description']); ?></textarea>
    </div>

    <?php if ($type === 'Complaint'): ?>
        <div class="mb-3">
            <label for="edit-priority" class="form-label"><i class="bi bi-exclamation-triangle"></i> Priority</label>
            <select id="edit-priority" name="priority" class="form-select">
                <option value="Low" <?php echo ($submission['priority'] ?? 'Medium') === 'Low' ? 'selected' : ''; ?>>Low</option>
                <option value="Medium" <?php echo ($submission['priority'] ?? 'Medium') === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                <option value="High" <?php echo ($submission['priority'] ?? 'Medium') === 'High' ? 'selected' : ''; ?>>High</option>
            </select>
        </div>

        <div class="mb-3">
            <div class="attachments-header">
                <h6 class="attachments-title">
                    <i class="bi bi-paperclip"></i>
                    Attachments
                </h6>
                <button type="button" class="add-attachment-btn" onclick="document.getElementById('new-attachment').click()">
                    <i class="bi bi-plus-circle"></i>
                    Add File
                </button>
            </div>
            
            <input type="file" id="new-attachment" name="new_attachment" accept=".jpg,.jpeg,.png,.mp4,.pdf,.doc,.docx" onchange="handleFileSelect(this)">
            
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
                            <button type="button" class="delete-attachment-btn" onclick="deleteAttachment(<?php echo $attachment['attachment_id']; ?>, this)" title="Delete attachment">
                                <i class="bi bi-x"></i>
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
                <i class="bi bi-info-circle"></i> Supported: Images (JPG, PNG), Videos (MP4), Documents (PDF, DOC) - Max 10MB
            </small>
        </div>
    <?php endif; ?>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-danger flex-grow-1">
            <i class="bi bi-check-circle"></i> Save Changes
        </button>
        <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancel
        </button>
    </div>
</form>

<script>
function handleFileSelect(input) {
    // Ensure we only allow up to 5 attachments total (existing + new)
    const attachmentsList = document.getElementById('attachmentsList');
    const existingCount = attachmentsList ? attachmentsList.querySelectorAll('.attachment-item[data-attachment-id]').length : 0;
    const newCount = attachmentsList ? attachmentsList.querySelectorAll('.attachment-item[data-new="1"]').length : 0;

    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const fileName = file.name;
    const fileSize = (file.size / (1024 * 1024)).toFixed(2); // MB

    if ((existingCount + newCount) >= 5) {
        Swal.fire({
            icon: 'error',
            title: 'Attachment limit',
            text: 'You can upload up to 5 attachments total. Please remove an existing file before adding another.'
        });
        // clear input
        input.value = '';
        return;
    }

    // Remove "no attachments" message if present
    const noMsg = document.getElementById('noAttachmentsMsg');
    if (noMsg) noMsg.remove();

    // Create preview element for the new file
    const preview = document.createElement('div');
    preview.className = 'attachment-item';
    preview.setAttribute('data-new', '1');
    preview.style.opacity = '0';
    preview.innerHTML = `
        <div class="attachment-info">
            <i class="bi bi-file-earmark"></i>
            <span class="attachment-name">${escapeHtml(fileName)}</span>
        </div>
        <div style="display:flex;gap:0.5rem;align-items:center;">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="viewSelectedFilePreview()" title="View">View</button>
            <button type="button" class="delete-attachment-btn" onclick="removeNewAttachment(this)" title="Remove">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `;

    attachmentsList.appendChild(preview);

    // animate in
    setTimeout(() => {
        preview.style.transition = 'all 0.25s';
        preview.style.opacity = '1';
        preview.style.transform = 'none';
    }, 20);

    // Inform user with a non-blocking toast-ish alert
    Swal.fire({
        title: 'File ready',
        html: `
            <div style="text-align:left">
                <p><strong>File:</strong> ${fileName}</p>
                <p><strong>Size:</strong> ${fileSize} MB</p>
                <p class="text-muted" style="font-size:0.85rem;">This file will be uploaded when you save changes.</p>
            </div>
        `,
        icon: 'info',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'OK'
    });

    // Update add button state
    updateAddButtonState();
}

function escapeHtml(unsafe) {
    return unsafe.replace(/[&<"'`=\/]/g, function (s) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;',
            '`': '&#x60;',
            '=': '&#x3D;'
        })[s];
    });
}

function viewSelectedFilePreview() {
    Swal.fire({
        title: 'Preview',
        text: 'Selected file will be uploaded when saving. For security browsers prevent showing local files here.',
        icon: 'info',
        confirmButtonColor: '#dc3545'
    });
}

function removeNewAttachment(button) {
    const item = button.closest('.attachment-item[data-new="1"]');
    if (!item) return;

    // remove the file input value
    const fileInput = document.getElementById('new-attachment');
    if (fileInput) fileInput.value = '';

    item.style.transition = 'all 0.25s';
    item.style.opacity = '0';
    item.style.transform = 'translateX(20px)';
    setTimeout(() => item.remove(), 250);

    updateAddButtonState();
}

function deleteAttachment(attachmentId, button) {
    Swal.fire({
        title: 'Delete Attachment?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('delete_attachment.php', {
                method: 'POST',
                credentials: 'same-origin', // ensure session cookie is sent
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ attachment_id: attachmentId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the attachment element from DOM with animation
                    const attachmentItem = button.closest('.attachment-item');
                    attachmentItem.style.transition = 'all 0.3s';
                    attachmentItem.style.opacity = '0';
                    attachmentItem.style.transform = 'translateX(20px)';
                    
                    setTimeout(() => {
                        attachmentItem.remove();
                        
                        // Check if there are no more attachments
                        const attachmentsList = document.getElementById('attachmentsList');
                            if (attachmentsList && attachmentsList.querySelectorAll('.attachment-item[data-attachment-id]').length === 0 && attachmentsList.querySelectorAll('.attachment-item[data-new="1"]').length === 0) {
                            attachmentsList.innerHTML = '<div class="no-attachments" id="noAttachmentsMsg"><i class="bi bi-inbox"></i> No attachments yet</div>';
                        }
                    }, 300);
                    
                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#dc3545',
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete attachment: ' + error.message,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

// Update add-attachment button state based on current count
function updateAddButtonState() {
    const attachmentsList = document.getElementById('attachmentsList');
    const addBtn = document.querySelector('.add-attachment-btn');
    if (!attachmentsList || !addBtn) return;

    const existing = attachmentsList.querySelectorAll('.attachment-item[data-attachment-id]').length;
    const pending = attachmentsList.querySelectorAll('.attachment-item[data-new="1"]').length;
    if ((existing + pending) >= 5) {
        addBtn.disabled = true;
        addBtn.title = 'Maximum 5 attachments allowed';
    } else {
        addBtn.disabled = false;
        addBtn.title = '';
    }
}

// Run on load to set initial state
document.addEventListener('DOMContentLoaded', function() {
    updateAddButtonState();
});
</script>