<?php
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/AdminMiddleware.php';
require_once __DIR__ . '/../classes/AdminService.php';
require_once __DIR__ . '/../classes/AdminCrudHandler.php';

// Only Super Admin can access
if (!isSuperAdmin()) {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access Denied']);
    exit;
}

$crudHandler = new AdminCrudHandler();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

switch ($action) {
    case 'create':
        $response = $crudHandler->create([
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => trim($_POST['password'] ?? ''),
            'role' => trim($_POST['role'] ?? 'ssc_admin')
        ]);
        break;

    case 'update':
        $response = $crudHandler->update([
            'admin_id' => intval($_POST['admin_id'] ?? 0),
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'role' => trim($_POST['role'] ?? 'ssc_admin')
        ]);
        break;

    case 'delete':
        $response = $crudHandler->delete([
            'admin_id' => intval($_POST['admin_id'] ?? 0)
        ]);
        break;

    default:
        $response = ['success' => false, 'message' => 'Invalid action'];
}

$message = urlencode($response['message']);
$type = !empty($response['success']) ? 'success' : 'error';

header("Location: ../index.php?page=profile&type={$type}&message={$message}");
exit;
?>