<?php
session_start();
require_once '../../db.php';
require_once '../classes/NotificationManager.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';
$notificationManager = new NotificationManager($_SESSION['user_id']);
$response = ['success' => false];

try {
    switch ($action) {
        case 'mark_as_read':
            $notificationId = intval($_POST['id'] ?? 0);
            if ($notificationManager->markAsRead($notificationId)) {
                $response['success'] = true;
            }
            break;

        case 'mark_all_as_read':
            if ($notificationManager->markAllAsRead()) {
                $response['success'] = true;
            }
            break;

        case 'delete':
            $notificationId = intval($_POST['id'] ?? 0);
            if ($notificationManager->deleteNotification($notificationId)) {
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