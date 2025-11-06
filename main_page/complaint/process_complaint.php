<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../signup_login/login.php");
    exit();
}

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../classes/Complaint.php';
require_once __DIR__ . '/../classes/Attachment.php';

$database = new Database();
$mysqli = $database->getConnection();

$student_id = $_SESSION['user_id'];

// Validate that student_id exists
if (empty($student_id)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Student ID not found in session. Please log in again.']);
    exit();
}

// Collect POST data safely
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$priority = isset($_POST['priority']) ? trim($_POST['priority']) : 'Medium';
$is_anonymous = isset($_POST['anonymous']) && ($_POST['anonymous'] === 'on' || $_POST['anonymous'] == '1') ? 1 : 0;

// Prepare data array without location
$data = [
    'category' => $category,
    'title' => $title,
    'description' => $description,
    'priority' => $priority,
    'is_anonymous' => $is_anonymous
];

$complaint = new Complaint($mysqli);

// Server-side validation
$valid = $complaint->validate($data, $student_id);
if (!$valid['success']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $valid['message']]);
    exit();
}

// Create complaint
$result = $complaint->create($data, $student_id);
if ($result['success']) {
    $complaint_id = $result['complaint_id'];
    
    if (isset($_FILES['attachment']) && is_array($_FILES['attachment']['name'])) {
        $attachment = new Attachment($mysqli);
        $fileCount = count($_FILES['attachment']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['attachment']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['attachment']['name'][$i],
                    'tmp_name' => $_FILES['attachment']['tmp_name'][$i],
                    'size' => $_FILES['attachment']['size'][$i],
                    'type' => $_FILES['attachment']['type'][$i],
                    'error' => $_FILES['attachment']['error'][$i]
                ];
                $uploadResult = $attachment->saveAttachment($file, $complaint_id);
                if (!$uploadResult['success']) {
                    // Log warning but continue - complaint is created
                }
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'complaint_id' => $complaint_id,
        'is_anonymous' => (bool)$is_anonymous,
        'message' => 'Complaint submitted successfully.'
    ]);
    exit();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $result['message']]);
    exit();
}
?>
