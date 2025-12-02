<?php

require_once __DIR__ . '/../../db.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['admin_role'])) {
    header('Location: ../../signup_login/login.php?redirect=admin');
    exit;
}

$admin_id = $_SESSION['admin_id'] ?? null;
$admin_role = $_SESSION['admin_role'] ?? null;
$admin_name = $_SESSION['first_name'] ?? 'Admin';
$admin_email = $_SESSION['authUser'] ?? null;
$admin_type = $_SESSION['admin_type'] ?? null;

if ($admin_email && !isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = $admin_email;
}

if ($admin_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        session_destroy();
        header('Location: ../../signup_login/login.php?message=' . urlencode('Database connection error'));
        exit;
    }
    
    $stmt = $db->prepare("
        SELECT is_active, name, role 
        FROM admin 
        WHERE admin_id = ? 
        LIMIT 1
    ");
    
    if (!$stmt) {
        session_destroy();
        header('Location: ../../signup_login/login.php?message=' . urlencode('Database error'));
        exit;
    }
    
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $stmt->close();
        
        if ($admin['role'] !== $_SESSION['admin_role']) {
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_type'] = $admin['role'] === 'super_admin' ? 'super_admin' : 'database_admin';
        }
        
        if (!empty($admin['name']) && $_SESSION['first_name'] !== $admin['name']) {
            $_SESSION['first_name'] = $admin['name'];
        }
        
        if ($admin['is_active'] != 1) {
            session_destroy();
            $message = ($admin['role'] === 'super_admin') 
                ? 'Super Admin account has been deactivated.'
                : 'Your account has been deactivated. Please contact Super Admin.';
            header('Location: ../../signup_login/login.php?message=' . urlencode($message));
            exit;
        }
    } else {
        $stmt->close();
        session_destroy();
        header('Location: ../../signup_login/login.php?message=' . urlencode('Admin account not found.'));
        exit;
    }
} else {
    session_destroy();
    header('Location: ../../signup_login/login.php?message=' . urlencode('Invalid session'));
    exit;
}

function isAdmin() {
    return isset($_SESSION['admin_role']) && in_array($_SESSION['admin_role'], ['super_admin', 'ssc_admin']);
}

function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

function isSSCAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'ssc_admin';
}

function requireAdminAccess() {
    if (!isAdmin()) {
        header('HTTP/1.0 403 Forbidden');
        echo "Access Denied";
        exit;
    }
}

function requireSuperAdminAccess() {
    if (!isSuperAdmin()) {
        header('HTTP/1.0 403 Forbidden');
        echo "Only Super Admin can access this page";
        exit;
    }
}

function requireSSCAdminAccess() {
    if (!isSSCAdmin() && !isSuperAdmin()) {
        header('HTTP/1.0 403 Forbidden');
        echo "Access Denied";
        exit;
    }
}
?>