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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$id = intval($_POST['id'] ?? 0);
$type = $_POST['type'] ?? '';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$priority = $_POST['priority'] ?? 'Medium';

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
$userProfile = new UserProfile($db, $_SESSION['user_id']);

// Prepare data array
$updateData = [
    'title' => $title,
    'description' => $description,
    'category' => $category,
    'priority' => $priority
];

// Update submission using class method
$result = $userProfile->updateSubmission($type, $id, $updateData);

if (!$result['success']) {
    http_response_code(500);
    echo json_encode($result);
    exit();
}

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