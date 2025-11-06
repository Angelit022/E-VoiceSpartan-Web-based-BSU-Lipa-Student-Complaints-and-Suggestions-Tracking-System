<?php
session_start();
require_once __DIR__ . '/classes/StudentService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $middle_initial = $_POST['middle_initial'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($student_id) || empty($phone_number) || empty($password) || empty($confirm_password)) {
        echo json_encode(['status' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if ($password !== $confirm_password) {
        echo json_encode(['status' => false, 'message' => 'Passwords do not match']);
        exit;
    }
    
    $studentService = new StudentService();
    $result = $studentService->registerStudent($first_name, $middle_initial, $last_name, $email, $student_id, $phone_number, $password);

    if (!$result['status']) {
        error_log("SIGNUP ERROR: " . $result['message']); // 👈 add this line
    }

    echo json_encode($result);

} else {
    echo json_encode(['status' => false, 'message' => 'Invalid request method']);
}
?>
