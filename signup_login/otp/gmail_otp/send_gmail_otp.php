<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/gmail_config.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode(['status' => false, 'message' => 'Database connection failed']);
    exit;
}

if (!isset($_SESSION['authUser'])) {
    echo json_encode(['status' => false, 'message' => 'Session expired. Please login again.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = isset($input['email']) ? trim($input['email']) : null;

if (!$email) {
    echo json_encode(['status' => false, 'message' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => false, 'message' => 'Invalid email format']);
    exit;
}

$otp_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

$stmt = $conn->prepare("INSERT INTO otp_verifications (student_id, email, otp_code, expires_at) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $_SESSION['authUser'], $email, $otp_code, $expires_at);
$stmt->execute();
$stmt->close();

if (sendMailOTP($email, $otp_code)) {
    echo json_encode(['status' => true, 'message' => 'OTP sent successfully']);
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to send OTP email. Check your email configuration.']);
}
?>
