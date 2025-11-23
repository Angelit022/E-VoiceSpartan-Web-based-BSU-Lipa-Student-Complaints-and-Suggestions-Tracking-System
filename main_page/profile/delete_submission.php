<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

require_once '../../db.php';
require_once '../classes/UserProfile.php';
require_once '../classes/StudentActivityLog.php';

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';
$id = intval($data['id'] ?? 0);

if (empty($type) || empty($id)) {
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Invalid request parameters']));
}

$student_id = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getConnection();
$userProfile = new UserProfile($conn, $student_id);

// Initialize activity logger
$activityLog = new StudentActivityLog($student_id);

// Use class method to delete submission
$result = $userProfile->deleteSubmission($type, $id);

// Log the deletion if successful
if ($result['success']) {
    if ($type === 'Complaint') {
        $activityLog->logComplaintDelete($id);
    } else if ($type === 'Suggestion') {
        $activityLog->logSuggestionDelete($id);
    }
}

header('Content-Type: application/json');
exit(json_encode($result));
?>