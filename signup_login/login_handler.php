<?php
session_start();
require_once __DIR__ . '/classes/StudentService.php';
require_once __DIR__ . '/classes/AdminAuthService.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Invalid request method']);
    exit;
}

$email_or_id = trim($_POST['student_id'] ?? '');
$password = trim($_POST['password'] ?? '');

error_log("[login_handler] Login attempt - Email/ID: $email_or_id");

if ($email_or_id === '' || $password === '') {
    echo json_encode(['status' => false, 'message' => 'ID/Email and password are required']);
    exit;
}

$adminAuth = new AdminAuthService();
if ($adminAuth->isAdminEmail($email_or_id)) {
    error_log("[login_handler] Detected as admin email");
    
    $adminResult = $adminAuth->authenticateAdmin($email_or_id, $password);

    if ($adminResult['status'] === true && isset($adminResult['data'])) {
        $admin = $adminResult['data'];
        $contact = $adminAuth->getAdminContactInfo($email_or_id);

        if (!$contact) {
            error_log("[login_handler] Admin contact info not found");
            echo json_encode(['status' => false, 'message' => 'Admin account not found']);
            exit;
        }

        $_SESSION['authUser'] = $admin['email'];
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_type'] = $adminResult['admin_type'];
        $_SESSION['first_name'] = $admin['name'];
        $_SESSION['phone_number'] = preg_replace('/\s+/', '', preg_replace('/^\+63/', '0', $contact['phone']));
        $_SESSION['logged_in'] = false;
        $_SESSION['is_admin'] = true;

        error_log("[login_handler] Admin session created - ID: {$admin['admin_id']}, Role: {$admin['role']}");

        echo json_encode([
            'status' => true,
            'message' => 'OTP verification required for admin',
            'is_admin' => true
        ]);
        exit;
    }

    error_log("[login_handler] Admin authentication failed: " . ($adminResult['message'] ?? 'Unknown error'));
    echo json_encode([
        'status' => false,
        'message' => $adminResult['message'] ?? 'Invalid admin credentials',
        'is_admin' => true
    ]);
    exit;
}

error_log("[login_handler] Attempting student login");
$studentService = new StudentService();
$result = $studentService->loginStudent($email_or_id, $password);

if (!is_array($result)) {
    $decoded = json_decode($result, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $result = $decoded;
    } else {
        echo json_encode(['status' => false, 'message' => 'Login Failed']);
        exit;
    }
}

if ($result['status'] === true && isset($result['data'])) {
    $student = $result['data'];

    $phone = isset($student['phone_number']) ? trim($student['phone_number']) : null;
    if ($phone && strlen($phone) >= 10) {
        $normalized_phone = preg_replace('/\s+/', '', $phone);
        $normalized_phone = preg_replace('/^\+63/', '0', $normalized_phone);
    } else {
        $normalized_phone = $phone ?? 'unknown';
    }

    $_SESSION['authUser'] = $student['student_id'];
    $_SESSION['first_name'] = $student['first_name'];
    $_SESSION['phone_number'] = $normalized_phone;
    $_SESSION['logged_in'] = false;
    $_SESSION['is_admin'] = false;

    error_log("[login_handler] Student session created - ID: {$student['student_id']}");

    echo json_encode([
        'status' => true,
        'message' => 'OTP verification required',
        'is_admin' => false
    ]);
    exit;
}

error_log("[login_handler] Login failed - neither admin nor student");
echo json_encode([
    'status' => false,
    'message' => $result['message'] ?? 'Invalid credentials'
]);
exit;
?>