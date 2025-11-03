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
    echo json_encode(['status' => false, 'message' => 'No OTP found. Please request again.']);
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

$_SESSION['logged_in'] = true;

$delete_stmt = $conn->prepare("DELETE FROM otp_verifications WHERE id = ?");
$delete_stmt->bind_param("i", $otp_record['id']);
$delete_stmt->execute();
$delete_stmt->close();

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
