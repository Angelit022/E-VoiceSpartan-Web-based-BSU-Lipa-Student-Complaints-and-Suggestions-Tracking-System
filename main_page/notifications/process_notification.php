<?php
session_start();
require_once '../../db.php';
require_once '../classes/NotificationManager.php';
require_once '../classes/StudentActivityLog.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$student_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$notificationManager = new NotificationManager($student_id);
$response = ['success' => false];

// Initialize activity logger
$activityLog = new StudentActivityLog($student_id);

try {
    switch ($action) {
        case 'mark_as_read':
            $notificationId = intval($_POST['id'] ?? 0);
            if ($notificationManager->markAsRead($notificationId)) {
                // Log notification mark as read
                $activityLog->logNotificationMarkRead($notificationId);
                
                $response['success'] = true;
            }
            break;

        case 'mark_all_as_read':
            if ($notificationManager->markAllAsRead()) {
                // Log mark all notifications as read
                $activityLog->logNotificationMarkAllRead();
                
                $response['success'] = true;
            }
            break;

        case 'delete':
            $notificationId = intval($_POST['id'] ?? 0);
            if ($notificationManager->deleteNotification($notificationId)) {
                // Log notification deletion
                $activityLog->logNotificationDelete($notificationId);
                
                $response['success'] = true;
            }
            break;

        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit();
?>