<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../db.php';

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

$input = $_POST ?? json_decode(file_get_contents('php://input'), true);
$otp_input = trim($input['otp'] ?? '');

if (!preg_match('/^\d{6}$/', $otp_input)) {
    echo json_encode(['status' => false, 'message' => 'Invalid OTP format']);
    exit;
}

$auth_user = $_SESSION['authUser'];
$is_admin = $_SESSION['is_admin'] ?? false;

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
    error_log("[verify_sms_otp] OTP mismatch - DB: '" . $otp_record['otp_code'] . "' vs Input: '" . $otp_input . "'");
    echo json_encode(['status' => false, 'message' => 'Incorrect OTP. Please try again.']);
    exit;
}

if ($is_admin) {
    $_SESSION['admin_id'] = $auth_user;
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['user_id'] = $auth_user;
    $_SESSION['logged_in'] = true;
} else {
    $_SESSION['user_id'] = $auth_user;
    $_SESSION['logged_in'] = true;
}

$delete_stmt = $conn->prepare("DELETE FROM otp_verifications WHERE id = ?");
$delete_stmt->bind_param("i", $otp_record['id']);
$delete_stmt->execute();
$delete_stmt->close();

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$basePath = dirname($_SERVER['SCRIPT_NAME'], 4);

if ($is_admin) {
    $redirect_url = $protocol . "://" . $host . $basePath . "/admin/index.php";
} else {
    $redirect_url = $protocol . "://" . $host . $basePath . "/main_page/homepage.php";
}

echo json_encode([
    'status' => true,
    'message' => 'Verification successful!',
    'redirect' => $redirect_url
]);
?>
