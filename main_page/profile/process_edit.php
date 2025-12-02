<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../db.php';
require_once '../classes/UserProfile.php';
require_once '../classes/Attachment.php';
require_once '../classes/StudentActivityLog.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$student_id = $_SESSION['user_id'];

if (!isset($_POST['id']) || !isset($_POST['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$id = intval($_POST['id']);
$type = trim($_POST['type']);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$priority = $_POST['priority'] ?? 'Medium';
$is_anonymous = isset($_POST['is_anonymous']) ? intval($_POST['is_anonymous']) : 0;

if (empty($id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid submission ID']);
    exit();
}

if (empty($type) || !in_array($type, ['Complaint', 'Suggestion'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid submission type']);
    exit();
}

if (empty($title)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit();
}

if (empty($description)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Description is required']);
    exit();
}

if (empty($category)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Category is required']);
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

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$userProfile = new UserProfile($db, $student_id);
$activityLog = new StudentActivityLog($student_id);

$updateData = [
    'title' => $title,
    'description' => $description,
    'category' => $category,
    'priority' => $priority,
    'is_anonymous' => $is_anonymous
];

$result = $userProfile->updateSubmission($type, $id, $updateData);

if (!$result['success']) {
    http_response_code(500);
    echo json_encode($result);
    exit();
}

if ($type === 'Complaint') {
    $activityLog->logComplaintEdit($id);
} else if ($type === 'Suggestion') {
    $activityLog->logSuggestionEdit($id);
}

if ($type === 'Complaint' && isset($_FILES['new_attachment']) && $_FILES['new_attachment']['error'] === UPLOAD_ERR_OK) {
    $attachmentHandler = new Attachment($db);
    $uploadResult = $attachmentHandler->saveAttachment($_FILES['new_attachment'], $id);
    
    if ($uploadResult['success']) {
        $filename = basename($_FILES['new_attachment']['name']);
        $activityLog->logAttachmentUpload($id, $filename);
    } else {
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