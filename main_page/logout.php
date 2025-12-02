<?php
session_start();
require_once __DIR__ . '/classes/StudentActivityLog.php';

// Log logout activity if user is logged in and confirmed
if (isset($_GET['confirmed']) && $_GET['confirmed'] === 'true' && isset($_SESSION['user_id'])) {
    $studentId = $_SESSION['user_id'];
    $activityLog = new StudentActivityLog($studentId);
    $activityLog->logStudentLogout();
}

session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');
header("Location: ../signup_login/login.php");
exit();
?>