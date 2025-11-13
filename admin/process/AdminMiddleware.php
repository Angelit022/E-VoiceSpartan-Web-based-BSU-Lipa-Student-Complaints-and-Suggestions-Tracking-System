<?php
/**
 * AdminMiddleware - Verify admin session and access control
 * Updated: Removed Staff role, SSC Admin has full response access
 */

require_once __DIR__ .'/../../db.php';

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


 // Check if user is any type of admin (Super Admin or SSC Admin)
function isAdmin() {
    return isset($_SESSION['admin_role']) && in_array($_SESSION['admin_role'], ['super_admin', 'ssc_admin']);
}

 //Check if user is Super Admin specifically
function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin' && isset($_SESSION['admin_type']) && $_SESSION['admin_type'] === 'super_admin';
}

 //Check if user is SSC Admin
function isSSCAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'ssc_admin';
}

/**
 * Require any admin access (Super Admin or SSC Admin)
 * Use this for pages/actions that both admin types can access
 */
function requireAdminAccess() {
    if (!isAdmin()) {
        header('HTTP/1.0 403 Forbidden');
        echo "Access Denied";
        exit;
    }
}

/**
 * Require Super Admin access only
 * Use this ONLY for admin management pages (creating/editing/deleting admins)
 */
function requireSuperAdminAccess() {
    if (!isSuperAdmin()) {
        header('HTTP/1.0 403 Forbidden');
        echo "Only Super Admin can access this page";
        exit;
    }
}

/**
 * Require SSC Admin or Super Admin access
 * Use this for response management and most admin functions
 */
function requireSSCAdminAccess() {
    if (!isSSCAdmin() && !isSuperAdmin()) {
        header('HTTP/1.0 403 Forbidden');
        echo "Access Denied";
        exit;
    }
}
?>