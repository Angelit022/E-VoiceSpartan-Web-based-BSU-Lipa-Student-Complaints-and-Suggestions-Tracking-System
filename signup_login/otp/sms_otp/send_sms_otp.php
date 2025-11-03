<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../db.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn || $conn->connect_error) {
    echo json_encode(['status' => false, 'message' => 'Database connection failed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$phone_number = isset($input['phone_number']) ? trim($input['phone_number']) : (isset($_SESSION['phone_number']) ? trim($_SESSION['phone_number']) : null);

if (!$phone_number) {
    echo json_encode(['status' => false, 'message' => 'Phone number required']);
    exit;
}

if (!preg_match('/^(\+63|0)?9\d{9}$/', str_replace(' ', '', $phone_number))) {
    echo json_encode(['status' => false, 'message' => 'Invalid phone number format.']);
    exit;
}

$phone_normalized = preg_replace('/^(\+63)/', '0', $phone_number);

$student_id = $_SESSION['authUser'];
$first_name = isset($_SESSION['first_name']) ? trim($_SESSION['first_name']) : "User";

$otp_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

$stmt = $conn->prepare("INSERT INTO otp_verifications (student_id, phone_number, otp_code, expires_at) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    error_log("[send_sms_otp] Prepare failed: " . $conn->error);
    echo json_encode(['status' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param("ssss", $student_id, $phone_normalized, $otp_code, $expires_at);
if (!$stmt->execute()) {
    error_log("[send_sms_otp] Execute failed: " . $stmt->error);
    echo json_encode(['status' => false, 'message' => 'Failed to generate OTP']);
    exit;
}
$stmt->close();

$iprog_api_token = "01d71561ff1632c0786db2c777e959af787c6d48";
$iprog_url = "https://sms.iprogtech.com/api/v1/otp/send_otp";

$custom_message = "Hello $first_name! Welcome to E-Voice Spartan. Your OTP is $otp_code. It's valid for 5 minutes, please don't share this code with anyone.";

$payload = [
    "api_token" => $iprog_api_token,
    "phone_number" => $phone_normalized,
    "message" => $custom_message
];

$ch = curl_init($iprog_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    error_log("[send_sms_otp] CURL Error: " . $curlError);
    echo json_encode(['status' => false, 'message' => 'SMS service error']);
    exit;
}

if ($httpCode === 200 && $response) {
    $res = json_decode($response, true);
    if (isset($res['status']) && $res['status'] === 'success') {

        $api_otp = isset($res['otp']) ? $res['otp'] : $otp_code;

        $update_stmt = $conn->prepare("UPDATE otp_verifications SET otp_code = ? WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
        $update_stmt->bind_param("ss", $api_otp, $student_id);
        $update_stmt->execute();
        $update_stmt->close();

        $_SESSION['otp_sent'] = true;
        $_SESSION['phone_number'] = $phone_normalized;

        error_log("[send_sms_otp] OTP sent successfully to: " . $phone_normalized);
        echo json_encode(['status' => true, 'message' => 'OTP sent successfully']);
    } else {
        error_log("[send_sms_otp] API Error: " . json_encode($res));
        echo json_encode(['status' => false, 'message' => 'Failed to send OTP']);
    }
} else {
    error_log("[send_sms_otp] HTTP Code: $httpCode Response: $response");
    echo json_encode(['status' => false, 'message' => 'SMS service error']);
}

?>
