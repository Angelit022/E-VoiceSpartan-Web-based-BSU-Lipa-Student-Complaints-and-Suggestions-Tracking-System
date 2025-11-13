<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../admin/classes/SuperAdminAccount.php';
require_once __DIR__ . '/../../classes/AdminAuthService.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn || $conn->connect_error) {
    echo json_encode(['status' => false, 'message' => 'Database connection failed.']);
    exit;
}

$phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : null;

if (!$phone_number) {
    echo json_encode(['status' => false, 'message' => 'Phone number required']);
    exit;
}

$phone_input = str_replace([' ', '-', '(', ')'], '', $phone_number);

// Convert all formats to 09XXXXXXXXX (standard local format)
if (strpos($phone_input, '+63') === 0) {
    $phone_normalized = '0' . substr($phone_input, 3); // +639123456789 -> 09123456789
} elseif (strpos($phone_input, '63') === 0 && strpos($phone_input, '+') !== 0) {
    $phone_normalized = '0' . substr($phone_input, 2); // 639123456789 -> 09123456789
} else {
    $phone_normalized = $phone_input; 
}

if (!preg_match('/^09\d{9}$/', $phone_normalized)) {
    echo json_encode(['status' => false, 'message' => 'Invalid phone number. Please use format: 09123456789']);
    exit;
}

$is_admin = $_SESSION['is_admin'] ?? false;
$auth_identifier = $_SESSION['authUser'] ?? null;
$first_name = isset($_SESSION['first_name']) ? trim($_SESSION['first_name']) : "User";

if (!$auth_identifier) {
    echo json_encode(['status' => false, 'message' => 'Session invalid. Please login again']);
    exit;
}

if ($is_admin) {
    // Admin validation
    $adminAuth = new AdminAuthService();
    $contact = $adminAuth->getAdminContactInfo($auth_identifier);
    
    if (!$contact) {
        echo json_encode(['status' => false, 'message' => 'Admin not found']);
        exit;
    }
    
    $registered_phone = $contact['phone'];
    // Normalize registered phone to 09XXXXXXXXX format
    $registered_phone = str_replace([' ', '-', '(', ')'], '', $registered_phone);
    if (strpos($registered_phone, '+63') === 0) {
        $registered_phone = '0' . substr($registered_phone, 3);
    } elseif (strpos($registered_phone, '63') === 0 && strpos($registered_phone, '+') !== 0) {
        $registered_phone = '0' . substr($registered_phone, 2);
    }
    
    // Check if phone matches registered admin phone
    if ($phone_normalized !== $registered_phone) {
        echo json_encode(['status' => false, 'message' => 'Please use your registered phone number: ' . $registered_phone]);
        exit;
    }
} else {
    $stmt = $conn->prepare("SELECT phone_number FROM student WHERE student_id = ? LIMIT 1");
    $stmt->bind_param("s", $auth_identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => false, 'message' => 'Student not found']);
        $stmt->close();
        exit;
    }
    
    $student = $result->fetch_assoc();
    $stmt->close();
    
    $registered_phone = $student['phone_number'];
    // Normalize registered phone to 09XXXXXXXXX format
    $registered_phone = str_replace([' ', '-', '(', ')'], '', $registered_phone);
    if (strpos($registered_phone, '+63') === 0) {
        $registered_phone = '0' . substr($registered_phone, 3);
    } elseif (strpos($registered_phone, '63') === 0 && strpos($registered_phone, '+') !== 0) {
        $registered_phone = '0' . substr($registered_phone, 2);
    }
    
    // Check if phone matches registered phone
    if ($phone_normalized !== $registered_phone) {
        echo json_encode(['status' => false, 'message' => 'Please use your registered phone number: ' . $registered_phone]);
        exit;
    }
}

$otp_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

if ($is_admin) {
    $stmt = $conn->prepare("INSERT INTO otp_verifications (email, phone_number, otp_code, expires_at) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        error_log("[send_sms_otp] Prepare failed: " . $conn->error);
        echo json_encode(['status' => false, 'message' => 'Database error']);
        exit;
    }
    $stmt->bind_param("ssss", $auth_identifier, $phone_normalized, $otp_code, $expires_at);
} else {
    $stmt = $conn->prepare("INSERT INTO otp_verifications (student_id, phone_number, otp_code, expires_at) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        error_log("[send_sms_otp] Prepare failed: " . $conn->error);
        echo json_encode(['status' => false, 'message' => 'Database error']);
        exit;
    }
    $stmt->bind_param("ssss", $auth_identifier, $phone_normalized, $otp_code, $expires_at);
}

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
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    error_log("[send_sms_otp] CURL Error: " . $curlError . " | Phone: " . $phone_normalized);
    echo json_encode(['status' => false, 'message' => 'SMS service connection error. Please try again.']);
    exit;
}

if (!$response) {
    error_log("[send_sms_otp] Empty response | HTTP Code: $httpCode | Phone: " . $phone_normalized);
    echo json_encode(['status' => false, 'message' => 'SMS service returned empty response. Please try again.']);
    exit;
}

$res = @json_decode($response, true);

if ($res === null) {
    error_log("[send_sms_otp] Invalid JSON response: " . $response . " | HTTP Code: $httpCode | Phone: " . $phone_normalized);
    echo json_encode(['status' => false, 'message' => 'SMS service error. Please try again.']);
    exit;
}

$isSuccess = false;
if (isset($res['status']) && strtolower($res['status']) === 'success') {
    $isSuccess = true;
} elseif (isset($res['success']) && $res['success'] === true) {
    $isSuccess = true;
} elseif ($httpCode === 200 && !isset($res['error'])) {
    $isSuccess = true;
}

if ($isSuccess) {
    // Update with API returned OTP if available
    $api_otp = isset($res['otp']) ? $res['otp'] : $otp_code;

    if ($is_admin) {
        $update_stmt = $conn->prepare("UPDATE otp_verifications SET otp_code = ? WHERE email = ? ORDER BY created_at DESC LIMIT 1");
    } else {
        $update_stmt = $conn->prepare("UPDATE otp_verifications SET otp_code = ? WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
    }
    $update_stmt->bind_param("ss", $api_otp, $auth_identifier);
    $update_stmt->execute();
    $update_stmt->close();

    $_SESSION['otp_sent'] = true;
    $_SESSION['phone_number'] = $phone_normalized;

    error_log("[send_sms_otp] OTP sent successfully to: " . $phone_normalized);
    echo json_encode(['status' => true, 'message' => 'OTP sent successfully to ' . $phone_normalized]);
} else {
    error_log("[send_sms_otp] API Error Response - HTTP Code: $httpCode | Response: " . json_encode($res) . " | Phone: " . $phone_normalized);
    $errorMsg = $res['message'] ?? $res['error'] ?? 'Failed to send OTP';
    echo json_encode(['status' => false, 'message' => 'Failed to send OTP: ' . $errorMsg]);
}
?>
