<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../classes/ActivityLogger.php';

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

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$otp_input = trim($input['otp'] ?? '');

if (!preg_match('/^\d{6}$/', $otp_input)) {
    echo json_encode(['status' => false, 'message' => 'Invalid OTP format']);
    exit;
}

$auth_user = $_SESSION['authUser'];
$is_admin = $_SESSION['is_admin'] ?? false;

$_SESSION['admin_role'] = $_SESSION['admin_role'] ?? $auth_user;

if ($is_admin) {
    $stmt = $conn->prepare("
        SELECT id, otp_code, expires_at
        FROM otp_verifications
        WHERE email = ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("s", $auth_user);
} else {
    $stmt = $conn->prepare("
        SELECT id, otp_code, expires_at
        FROM otp_verifications
        WHERE student_id = ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("s", $auth_user);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => false, 'message' => 'No OTP found. Please request again.']);
    exit;
}

$otp_record = $result->fetch_assoc();
$stmt->close();

if (strtotime($otp_record['expires_at']) < time()) {
    echo json_encode(['status' => false, 'message' => 'OTP expired. Please request a new one.']);
    exit;
}

if (trim($otp_record['otp_code']) !== trim($otp_input)) {
    error_log("[verify_gmail_otp] OTP mismatch - DB: '" . $otp_record['otp_code'] . "' vs Input: '" . $otp_input . "'");
    echo json_encode(['status' => false, 'message' => 'Incorrect OTP. Please try again.']);
    exit;
}

// Set session variables
if ($is_admin) {
    $_SESSION['admin_id'] = $auth_user;
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['user_id'] = $auth_user;
    $_SESSION['logged_in'] = true;
} else {
    $_SESSION['user_id'] = $auth_user;
    $_SESSION['logged_in'] = true;
}

// Log the successful login activity
$activityLogger = new ActivityLogger();
$user_type = $is_admin ? 'admin' : 'student';
$activityLogger->logLogin($auth_user, $user_type, 'Gmail OTP');

// Delete used OTP
$delete_stmt = $conn->prepare("DELETE FROM otp_verifications WHERE id = ?");
$delete_stmt->bind_param("i", $otp_record['id']);
$delete_stmt->execute();
$delete_stmt->close();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

$script_path = $_SERVER['SCRIPT_NAME'];
$base_path = '';

$pos = strpos($script_path, '/signup_login');
if ($pos !== false) {
    $base_path = substr($script_path, 0, $pos);
}

if ($is_admin) {
    $redirect_url = "$protocol://$host$base_path/admin/index.php";
} else {
    $redirect_url = "$protocol://$host$base_path/main_page/homepage.php";
}

error_log("[verify_gmail_otp] Redirect URL: $redirect_url");

echo json_encode([
    'status' => true,
    'message' => 'Verification successful!',
    'redirect' => $redirect_url
]);
?>