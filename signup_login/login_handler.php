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

if ($email_or_id === '' || $password === '') {
    echo json_encode(['status' => false, 'message' => 'ID/Email and password are required']);
    exit;
}

$adminAuth = new AdminAuthService();
if ($adminAuth->isAdminEmail($email_or_id)) {
    $adminResult = $adminAuth->authenticateAdmin($email_or_id, $password);

    if ($adminResult['status'] === true && isset($adminResult['data'])) {
        $admin = $adminResult['data'];
        $contact = $adminAuth->getAdminContactInfo($email_or_id);

        if (!$contact) {
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

        echo json_encode([
            'status' => true,
            'message' => 'OTP verification required for admin',
            'is_admin' => true
        ]);
        exit;
    }

    echo json_encode([
        'status' => false,
        'message' => $adminResult['message'] ?? 'Invalid admin credentials',
        'is_admin' => true
    ]);
    exit;
}

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

    echo json_encode([
        'status' => true,
        'message' => 'OTP verification required',
        'is_admin' => false
    ]);
    exit;
}

echo json_encode([
    'status' => false,
    'message' => $result['message'] ?? 'Invalid credentials'
]);
exit;
?>