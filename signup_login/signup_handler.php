<?php
session_start();
require_once __DIR__ . '/classes/GoogleAuthService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['google_signup_data'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid session. Please start over.']);
    exit;
}

$data = $_SESSION['google_signup_data'];
$step = $_POST['step'] ?? '';

try {
    
    if ($step === 'mobile') {
        // STEP 1: Validate mobile number
        $phone_number = trim($_POST['phone_number'] ?? '');
        
        if (!preg_match('/^09\d{9}$/', $phone_number)) {
            echo json_encode(['status' => false, 'message' => 'Invalid phone number format. Must be 11 digits starting with 09']);
            exit;
        }
        
        $_SESSION['google_signup_data']['phone_number'] = $phone_number;
        $_SESSION['signup_step'] = 'password';
        
        echo json_encode(['status' => true, 'message' => 'Mobile number validated successfully']);
        exit;
        
    } elseif ($step === 'password') {
        // STEP 2: Validate and save password
        $password = $_POST['password'] ?? '';
        
        if (!preg_match('/^[a-f0-9]{64}$/i', $password)) {
            echo json_encode(['status' => false, 'message' => 'Invalid password format. Please try again.']);
            exit;
        }
        
        if (!isset($data['phone_number'])) {
            echo json_encode(['status' => false, 'message' => 'Phone number missing. Please start over.']);
            exit;
        }
        
        $googleAuth = new GoogleAuthService();
        $result = $googleAuth->completeRegistration(
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['student_id'],
            $data['google_id'],
            $data['phone_number'],
            $password  
        );
        
        if ($result['status']) {
            unset($_SESSION['google_signup_data']);
            unset($_SESSION['signup_step']);
        }
        
        echo json_encode($result);
        exit;
    }
    
    echo json_encode(['status' => false, 'message' => 'Invalid request']);
    exit;
    
} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => 'An error occurred. Please try again.']);
    exit;
}
?>