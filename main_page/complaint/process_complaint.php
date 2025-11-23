<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User session not found. Please log in again.']);
    exit();
}

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../classes/Complaint.php';
require_once __DIR__ . '/../classes/Attachment.php';
require_once __DIR__ . '/../classes/StudentActivityLog.php';

try {
    $database = new Database();
    $mysqli = $database->getConnection();
    
    if (!$mysqli) {
        throw new Exception('Database connection failed');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]);
    exit();
}

$student_id = $_SESSION['user_id'];

if (empty($student_id)) {
    echo json_encode(['success' => false, 'message' => 'Student ID not found in session.']);
    exit();
}

// Initialize activity logger
$activityLog = new StudentActivityLog($student_id);

$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$priority = isset($_POST['priority']) ? trim($_POST['priority']) : '';
$is_anonymous = (isset($_POST['anonymous']) && $_POST['anonymous'] === '1') ? 1 : 0;

// Validate required fields
if (empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Category is required.']);
    exit();
}

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required.']);
    exit();
}

if (empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Description is required.']);
    exit();
}

if (empty($priority)) {
    echo json_encode(['success' => false, 'message' => 'Priority is required.']);
    exit();
}

$data = [
    'category' => $category,
    'title' => $title,
    'description' => $description,
    'priority' => $priority,
    'is_anonymous' => $is_anonymous
];

try {
    $complaint = new Complaint($mysqli);

    // Server-side validation
    $valid = $complaint->validate($data, $student_id);
    if (!$valid['success']) {
        echo json_encode(['success' => false, 'message' => $valid['message']]);
        exit();
    }

    // Create complaint
    $result = $complaint->create($data, $student_id);
    
    if (!$result['success']) {
        echo json_encode(['success' => false, 'message' => $result['message']]);
        exit();
    }
    
    $complaint_id = $result['complaint_id'];
    
    // Log complaint creation
    $activityLog->logComplaintCreate($complaint_id, $is_anonymous);
    
    // Handle file upload if present
    $fileUploaded = false;
    $fileMessage = '';
    
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $attachment = new Attachment($mysqli);
            $uploadResult = $attachment->saveAttachment($_FILES['attachment'], $complaint_id);
            
            if ($uploadResult['success']) {
                $fileUploaded = true;
                $fileMessage = ' File uploaded successfully.';
                
                // Log attachment upload
                $filename = basename($_FILES['attachment']['name']);
                $activityLog->logAttachmentUpload($complaint_id, $filename);
            } else {
                $fileMessage = ' Warning: ' . $uploadResult['message'];
            }
        } else {
            $fileMessage = ' Warning: File upload error code ' . $_FILES['attachment']['error'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'complaint_id' => $complaint_id,
        'is_anonymous' => (bool)$is_anonymous,
        'message' => 'Complaint submitted successfully.' . $fileMessage,
        'file_uploaded' => $fileUploaded
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

exit();
?>