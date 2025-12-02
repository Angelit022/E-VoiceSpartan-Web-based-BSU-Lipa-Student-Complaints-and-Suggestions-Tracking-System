<?php
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/AdminMiddleware.php';
require_once __DIR__ . '/../classes/AdminService.php';
require_once __DIR__ . '/../classes/AdminActivityLog.php';

if (!isSuperAdmin()) {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access Denied']);
    exit;
}

$currentAdminId = $_SESSION['admin_id'] ?? null;
$currentAdminName = $_SESSION['first_name'] ?? 'Admin';

if ($currentAdminId) {
    $database = new Database();
    $db = $database->getConnection();
    if ($db) {
        $stmt = $db->prepare("SELECT name FROM admin WHERE admin_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $currentAdminId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result) {
                $currentAdminName = $result['name'];
            }
            $stmt->close();
        }
    }
}

$activityLog = new AdminActivityLog($currentAdminId, $currentAdminName);

$adminService = new AdminService();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

switch ($action) {
    case 'create':
        $response = $adminService->create([
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => trim($_POST['password'] ?? ''),
            'role' => trim($_POST['role'] ?? 'ssc_admin')
        ]);

        if ($response['success'] && isset($response['admin_id'])) {
            $activityLog->logAdminCreate(
                $response['admin_id'],
                trim($_POST['name'] ?? ''),
                trim($_POST['email'] ?? '')
            );
        }
        break;

    case 'update':
        $targetAdminId = intval($_POST['admin_id'] ?? 0);

        $oldAdminData = $adminService->getAdminById($targetAdminId);
        
        $response = $adminService->update([
            'admin_id' => $targetAdminId,
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'role' => trim($_POST['role'] ?? 'ssc_admin'),
            'is_active' => intval($_POST['is_active'] ?? 1)
        ]);

        if ($response['success'] && $oldAdminData) {
            $changes = [];
            $newData = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'is_active' => intval($_POST['is_active'] ?? 1)
            ];
            
            if ($oldAdminData['name'] !== $newData['name']) {
                $changes[] = "Name: '{$oldAdminData['name']}' → '{$newData['name']}'";
            }
            if ($oldAdminData['email'] !== $newData['email']) {
                $changes[] = "Email: '{$oldAdminData['email']}' → '{$newData['email']}'";
            }
            if ($oldAdminData['phone_number'] !== $newData['phone']) {
                $changes[] = "Phone: '{$oldAdminData['phone_number']}' → '{$newData['phone']}'";
            }
            if ((int)$oldAdminData['is_active'] !== $newData['is_active']) {
                $oldStatus = $oldAdminData['is_active'] ? 'Active' : 'Inactive';
                $newStatus = $newData['is_active'] ? 'Active' : 'Inactive';
                $changes[] = "Status: {$oldStatus} → {$newStatus}";
            }
            
            $activityLog->logAdminUpdate(
                $targetAdminId,
                $oldAdminData['name'],
                $changes
            );
        }
        break;

    case 'delete':
        $targetAdminId = intval($_POST['admin_id'] ?? 0);

        $targetAdminData = $adminService->getAdminById($targetAdminId);
        
        $response = $adminService->delete([
            'admin_id' => $targetAdminId
        ]);
        
        if ($response['success'] && $targetAdminData) {
            if (strpos($response['message'], 'deactivated') !== false) {
                $activityLog->logAdminDeactivate(
                    $targetAdminId,
                    $targetAdminData['name']
                );
            } else {
                $activityLog->logAdminDelete(
                    $targetAdminId,
                    $targetAdminData['name']
                );
            }
        }
        break;

    default:
        $response = ['success' => false, 'message' => 'Invalid action'];
}

$message = urlencode($response['message']);
$type = !empty($response['success']) ? 'success' : 'error';

header("Location: ../index.php?page=profile&type={$type}&message={$message}");
exit;
?>