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
require_once '../classes/StudentActivityLog.php';

$student_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();
$userProfile = new UserProfile($db, $student_id);

// Initialize activity logger
$activityLog = new StudentActivityLog($student_id);

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'check_status':
        $id = intval($_GET['id'] ?? 0);
        $type = $_GET['type'] ?? '';

        if (empty($id) || empty($type)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit();
        }

        $result = $userProfile->checkFeedbackStatus($type, $id);
        
        if (!$result['success']) {
            http_response_code(403);
        }
        
        echo json_encode($result);
        break;

    case 'check_all_status':
        $result = $userProfile->checkAllFeedbackStatus();
        echo json_encode($result);
        break;

    case 'submit_feedback':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);
        $type = $input['type'] ?? '';
        $rating = intval($input['rating'] ?? 0);

        if (empty($id) || empty($type) || $rating < 1 || $rating > 5) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit();
        }

        $result = $userProfile->submitFeedback($type, $id, $rating);
        
        if ($result['success']) {
            // Log feedback submission
            $activityLog->logFeedbackSubmit($type, $id, $rating);
        } else {
            http_response_code(400);
        }
        
        echo json_encode($result);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
exit();
?>