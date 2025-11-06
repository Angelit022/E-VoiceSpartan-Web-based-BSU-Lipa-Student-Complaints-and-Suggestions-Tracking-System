<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/gmail_config.php';

error_log("[verify_gmail_otp] Request started");

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

$database = new Database();
$conn = $database->getConnection();
if (!$conn) {
    echo json_encode(['status' => false, 'message' => 'Database connection failed']);
    exit;
}

$student_id = $_SESSION['authUser'];

$stmt = $conn->prepare("
    SELECT id, otp_code, expires_at
    FROM otp_verifications
    WHERE student_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");

$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => false, 'message' => 'No OTP found. Please request a new one.']);
    exit;
}

$otp_record = $result->fetch_assoc();
$stmt->close();

if (strtotime($otp_record['expires_at']) < time()) {
    echo json_encode(['status' => false, 'message' => 'OTP expired. Please request a new one.']);
    exit;
}

if ($otp_record['otp_code'] !== $otp_input) {
    echo json_encode(['status' => false, 'message' => 'Incorrect OTP. Please try again.']);
    exit;
}

$_SESSION['user_id'] = $student_id;
$_SESSION['logged_in'] = true;

$delete_stmt = $conn->prepare("DELETE FROM otp_verifications WHERE id = ?");
$delete_stmt->bind_param("i", $otp_record['id']);
$delete_stmt->execute();
$delete_stmt->close();

error_log("[verify_gmail_otp] OTP verified successfully for user: $student_id");

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
          . "://" . $_SERVER['HTTP_HOST']
          . str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'], 3)); 

$redirect_url = $base_url . "/../main_page/homepage.php";

echo json_encode([
    'status' => true,
    'message' => 'Verification successful!',
    'redirect' => $redirect_url
]);

?>
