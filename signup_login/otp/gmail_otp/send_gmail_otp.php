<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/gmail_config.php';
require_once __DIR__ . '/../../classes/AdminAuthService.php';

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

$is_admin = $_SESSION['is_admin'] ?? false;
$auth_user = $_SESSION['authUser'];

if ($is_admin) {
    $adminAuth = new AdminAuthService();
    $contact = $adminAuth->getAdminContactInfo($auth_user);
    
    if (!$contact) {
        echo json_encode(['status' => false, 'message' => 'Admin not found']);
        exit;
    }
    
    if (strtolower($email) !== strtolower($contact['email'])) {
        echo json_encode(['status' => false, 'message' => 'Please use your registered email: ' . $contact['email']]);
        exit;
    }
} else {
    $stmt = $conn->prepare("SELECT email FROM student WHERE student_id = ? LIMIT 1");
    $stmt->bind_param("s", $auth_user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => false, 'message' => 'Student not found']);
        $stmt->close();
        exit;
    }
    
    $student = $result->fetch_assoc();
    $stmt->close();
    
    $registered_email = $student['email'];
    
    if (strtolower($email) !== strtolower($registered_email)) {
        echo json_encode(['status' => false, 'message' => 'Please use your registered G-Suite email: ' . $registered_email]);
        exit;
    }
    
    if (strpos($email, '@g.batstate-u.edu.ph') === false) {
        echo json_encode(['status' => false, 'message' => 'Please use your G-Suite account (ending with @g.batstate-u.edu.ph)']);
        exit;
    }
}

$otp_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

if ($is_admin) {
    $stmt = $conn->prepare("INSERT INTO otp_verifications (email, otp_code, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $otp_code, $expires_at);
} else {
    $stmt = $conn->prepare("INSERT INTO otp_verifications (student_id, email, otp_code, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $auth_user, $email, $otp_code, $expires_at);
}

$stmt->execute();
$stmt->close();

if (sendMailOTP($email, $otp_code)) {
    echo json_encode(['status' => true, 'message' => 'OTP sent successfully']);
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to send OTP email. Check your email configuration.']);
}
?>