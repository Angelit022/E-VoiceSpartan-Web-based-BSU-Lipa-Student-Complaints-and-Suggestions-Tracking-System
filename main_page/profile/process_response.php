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

$activityLog = new StudentActivityLog($student_id);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_responses':
            $id = intval($_GET['id'] ?? 0);
            $type = $_GET['type'] ?? '';

            if (empty($id) || empty($type)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
                exit();
            }

            $result = $userProfile->getResponses($type, $id);
            
            if (!$result['success']) {
                http_response_code(403);
            }
            
            echo json_encode($result);
            break;

        case 'check_status':
            $result = $userProfile->checkResponseStatus();
            echo json_encode($result);
            break;

        case 'mark_viewed':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);
            $type = $input['type'] ?? '';

            if (empty($id) || empty($type)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
                exit();
            }

            $result = $userProfile->markResponseViewed($type, $id);
            
            if ($result['success']) {
                $activityLog->logResponseView($type, $id);
            } else {
                http_response_code(403);
            }
            
            echo json_encode($result);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
exit();
?>