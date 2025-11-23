<?php
session_start();
require_once __DIR__ . '/../admin/classes/AdminActivityLog.php';

// Log logout activity before destroying session
if (isset($_SESSION['admin_id']) && isset($_SESSION['first_name'])) {
    $adminId = $_SESSION['admin_id'];
    $adminName = $_SESSION['first_name'];
    $activityLog = new AdminActivityLog($adminId, $adminName);
    $activityLog->logAdminLogout();
}

session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');
header("Location: ../signup_login/login.php");
exit();
?>