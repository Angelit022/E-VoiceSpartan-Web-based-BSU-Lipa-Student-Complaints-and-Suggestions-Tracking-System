<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

require_once '../../db.php';
require_once '../classes/SettingsManager.php';
require_once '../classes/ValidationManager.php';
require_once '../classes/NotificationManager.php';
require_once '../classes/StudentActivityLog.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Please log in again']);
    exit();
}

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    $studentId = $_SESSION['user_id'];
    
    if (empty($studentId)) {
        throw new Exception('Invalid session - student ID is empty');
    }
    
    $activityLog = new StudentActivityLog($studentId);
    
    switch ($action) {
case 'update_account':
    $settingsManager = new SettingsManager($studentId);
    $validator = new ValidationManager();
    
    $phoneNumber = $validator->sanitizeInput($_POST['phoneNumber'] ?? '');

    if (!empty($phoneNumber) && !$validator->validatePhone($phoneNumber)) {
        throw new Exception(implode(', ', $validator->getErrors()));
    }

    if ($settingsManager->updatePhoneNumber($phoneNumber)) {
        $activityLog->logSettingsUpdate('account');
        
        $response['success'] = true;
        $response['message'] = 'Mobile number updated successfully!';
    } else {
        throw new Exception('Failed to update mobile number');
    }
    break;

        case 'update_password':
            $settingsManager = new SettingsManager($studentId);
            $validator = new ValidationManager();
            
            $currentPassword = $_POST['currentPassword'] ?? '';
            $newPassword = $_POST['newPassword'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';

            if (!$validator->validatePassword($currentPassword)) {
                throw new Exception(implode(', ', $validator->getErrors()));
            }
            $validator->clearErrors();

            if (!$settingsManager->verifyPassword($currentPassword)) {
                throw new Exception('Current password is incorrect');
            }

            if (!$validator->validatePassword($newPassword, 6)) {
                throw new Exception(implode(', ', $validator->getErrors()));
            }
            $validator->clearErrors();

            if (!$validator->validatePasswordMatch($newPassword, $confirmPassword)) {
                throw new Exception(implode(', ', $validator->getErrors()));
            }

            if ($settingsManager->updatePassword($newPassword)) {
                $activityLog->logSettingsUpdate('password');
                
                $response['success'] = true;
                $response['message'] = 'Password updated successfully!';
            } else {
                throw new Exception('Failed to update password');
            }
            break;

        case 'update_notifications':
            $viaEmail = isset($_POST['viaEmail']) && $_POST['viaEmail'] === 'on';
            $viaSms = isset($_POST['viaSms']) && $_POST['viaSms'] === 'on';

            $database = new Database();
            $db = $database->getConnection();
            $checkStmt = $db->prepare("SELECT student_id FROM student WHERE student_id = ?");
            
            if (!$checkStmt) {
                throw new Exception('Database error - could not verify student account');
            }
            
            $checkStmt->bind_param("s", $studentId);
            $checkStmt->execute();
            $studentExists = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();
            
            if (!$studentExists) {
                throw new Exception('Your account could not be found. Please try logging in again.');
            }

            $notificationManager = new NotificationManager($studentId);
            if ($notificationManager->updatePreferences($viaEmail, $viaSms)) {
                $activityLog->logSettingsUpdate('notifications');
                
                $response['success'] = true;
                $response['message'] = 'Notification preferences updated successfully!';
            } else {
                throw new Exception('Failed to save notification preferences. Please check your account status.');
            }
            break;

        case 'update_preferences':
            $settingsManager = new SettingsManager($studentId);
            $validator = new ValidationManager();
            
            $language = $validator->sanitizeInput($_POST['language'] ?? 'en');
            $theme = $validator->sanitizeInput($_POST['theme'] ?? 'system');

            if ($settingsManager->updatePreferences($language, $theme)) {
                $activityLog->logSettingsUpdate('preferences');
                
                $response['success'] = true;
                $response['message'] = 'Preferences updated successfully!';
            } else {
                throw new Exception('Failed to update preferences');
            }
            break;

        default:
            throw new Exception('Invalid action specified');
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit();
?>