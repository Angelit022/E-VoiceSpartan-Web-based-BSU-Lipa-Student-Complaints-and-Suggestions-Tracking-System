<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../db.php';
require_once '../classes/Attachment.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get form data
$id = intval($_POST['id'] ?? 0);
$type = $_POST['type'] ?? '';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$priority = $_POST['priority'] ?? null;

// Validation
if (empty($id) || empty($type) || empty($title) || empty($description)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

if (!in_array($type, ['Complaint', 'Suggestion'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid submission type']);
    exit();
}

if (strlen($title) > 255) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title must be 255 characters or less']);
    exit();
}

if (strlen($description) < 10) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Description must be at least 10 characters']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Verify ownership and status
if ($type === 'Complaint') {
    $tableName = 'complaint';
    $primaryKey = 'complaint_id';
} else {
    $tableName = 'suggestion';
    $primaryKey = 'suggestion_id';
}

$verify_query = "SELECT student_id, status_id FROM $tableName WHERE $primaryKey = ?";
$verify_stmt = $db->prepare($verify_query);
$verify_stmt->bind_param("i", $id);
$verify_stmt->execute();
$result = $verify_stmt->get_result();
$submission = $result->fetch_assoc();
$verify_stmt->close();

if (!$submission) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Submission not found']);
    exit();
}

if ($submission['student_id'] !== $user_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($submission['status_id'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only pending submissions can be edited']);
    exit();
}

// Update submission
if ($type === 'Complaint') {
    $update_query = "UPDATE complaint SET title = ?, description = ?, category = ?, priority = ? WHERE complaint_id = ? AND student_id = ?";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bind_param("ssssss", $title, $description, $category, $priority, $id, $user_id);
} else {
    $update_query = "UPDATE suggestion SET title = ?, description = ?, category = ? WHERE suggestion_id = ? AND student_id = ?";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bind_param("sssss", $title, $description, $category, $id, $user_id);
}

if (!$update_stmt->execute()) {
    $update_stmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update submission']);
    exit();
}
$update_stmt->close();

// Handle new attachment upload for complaints
if ($type === 'Complaint' && isset($_FILES['new_attachment']) && $_FILES['new_attachment']['error'] === UPLOAD_ERR_OK) {
    $attachmentHandler = new Attachment($db);
    $uploadResult = $attachmentHandler->saveAttachment($_FILES['new_attachment'], $id);
    
    if (!$uploadResult['success']) {

        echo json_encode([
            'success' => true, 
            'message' => 'Submission updated successfully, but attachment upload failed: ' . $uploadResult['message']
        ]);
        exit();
    }
}

echo json_encode(['success' => true, 'message' => 'Submission updated successfully']);
exit();
?>