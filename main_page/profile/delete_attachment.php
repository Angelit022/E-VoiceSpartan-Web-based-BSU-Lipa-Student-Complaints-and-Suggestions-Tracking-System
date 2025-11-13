<?php
session_start();

// Set JSON header at the very beginning
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../db.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['attachment_id']) || empty($input['attachment_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing attachment ID']);
    exit();
}

$attachment_id = intval($input['attachment_id']);

if ($attachment_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid attachment ID']);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$query = "SELECT a.file_path, c.student_id, c.status_id
          FROM attachment a 
          INNER JOIN complaint c ON a.complaint_id = c.complaint_id 
          WHERE a.attachment_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $attachment_id);
$stmt->execute();
$result = $stmt->get_result();
$attachment = $result->fetch_assoc();
$stmt->close();

if (!$attachment) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Attachment not found']);
    exit();
}


if ($attachment['student_id'] != $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}


if ($attachment['status_id'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Cannot delete attachments from non-pending submissions']);
    exit();
}


$file_path = "../../" . $attachment['file_path'];
$fileDeleted = false;

if (file_exists($file_path)) {
    $fileDeleted = @unlink($file_path);
}


$delete_query = "DELETE FROM attachment WHERE attachment_id = ?";
$delete_stmt = $conn->prepare($delete_query);

if (!$delete_stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$delete_stmt->bind_param("i", $attachment_id);

if ($delete_stmt->execute()) {
    $delete_stmt->close();
    
    $message = 'Attachment deleted successfully';
    if (!$fileDeleted && file_exists($file_path)) {
        $message .= ' (file could not be removed from server)';
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    $delete_stmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete attachment from database']);
}

exit();
?>