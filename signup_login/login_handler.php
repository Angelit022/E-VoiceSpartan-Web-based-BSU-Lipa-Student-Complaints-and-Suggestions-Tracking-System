<?php
session_start();
require_once __DIR__ . '/classes/StudentService.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Invalid request method']);
    exit;
}

$student_id = trim($_POST['student_id'] ?? '');
$password   = trim($_POST['password'] ?? '');

if ($student_id === '' || $password === '') {
    echo json_encode(['status' => false, 'message' => 'Student ID and password are required']);
    exit;
}

$studentService = new StudentService();
$result = $studentService->loginStudent($student_id, $password);

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

    $_SESSION['authUser']     = $student['student_id'];
    $_SESSION['first_name']   = $student['first_name'];
    $_SESSION['phone_number'] = $normalized_phone;
    $_SESSION['logged_in']    = false;

    echo json_encode([
        'status'  => true,
        'message' => 'OTP verification required'
    ]);
    exit;
}

echo json_encode([
    'status'  => false,
    'message' => $result['message'] ?? 'Invalid credentials'
]);
exit;
?>
