<?php
/**
 * validate_contact.php - Validate if the requested email/phone matches user's registered info
 * This prevents unauthorized OTP requests
 * Updated: Removed Staff role references
 */
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../classes/AdminAuthService.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Invalid request']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$contact_type = $input['contact_type'] ?? ''; // 'email' or 'phone'
$contact_value = trim($input['contact_value'] ?? '');
$user_id = $_SESSION['authUser'] ?? '';
$is_admin = $_SESSION['is_admin'] ?? false;

error_log("[validate_contact] START - contact_type: $contact_type, user_id: $user_id, is_admin: $is_admin");

if (empty($contact_type) || empty($contact_value) || empty($user_id)) {
    error_log("[validate_contact] Missing fields - contact_type: $contact_type, contact_value: $contact_value, user_id: $user_id");
    echo json_encode(['status' => false, 'message' => 'Missing required fields']);
    exit;
}

$db = (new Database())->getConnection();

if (!$db) {
    error_log("[validate_contact] Database connection failed");
    echo json_encode(['status' => false, 'message' => 'Database error']);
    exit;
}

if ($is_admin) {
    // Admin validation - Only Super Admin and SSC Admin
    $admin_type = $_SESSION['admin_type'] ?? '';
    error_log("[validate_contact] Admin validation - admin_type: $admin_type");
    
    if ($admin_type === 'super_admin') {
        // Super Admin must use their specific email/phone
        $adminAuth = new AdminAuthService();
        $contact_info = $adminAuth->getAdminContactInfo($user_id);
        
        if (!$contact_info) {
            error_log("[validate_contact] Super admin not found for: $user_id");
            echo json_encode(['status' => false, 'message' => 'Admin not found']);
            exit;
        }
        
        $valid = false;
        $error_msg = '';
        
        if ($contact_type === 'email') {
            if (strtolower($contact_value) !== strtolower($contact_info['email'])) {
                $error_msg = "As Super Admin, please use your registered email: {$contact_info['email']}";
                error_log("[validate_contact] Super Admin email mismatch - input: $contact_value, registered: {$contact_info['email']}");
            } else {
                $valid = true;
                error_log("[validate_contact] Super Admin email verified");
            }
        } else if ($contact_type === 'phone') {
            $normalized_input = preg_replace('/\D/', '', $contact_value);
            $normalized_registered = preg_replace('/\D/', '', $contact_info['phone']);
            
            if ($normalized_input !== $normalized_registered) {
                $error_msg = "As Super Admin, please use your registered phone number: {$contact_info['phone']}";
                error_log("[validate_contact] Super Admin phone mismatch - input: $normalized_input, registered: $normalized_registered");
            } else {
                $valid = true;
                error_log("[validate_contact] Super Admin phone verified");
            }
        }
        
        echo json_encode([
            'status' => $valid,
            'message' => $error_msg ?: 'Verified',
            'type' => 'super_admin'
        ]);
    } else {
        // Database Admin (ssc_admin only)
        $stmt = $db->prepare("
            SELECT email, phone_number, role FROM admin 
            WHERE LOWER(email) = LOWER(?) AND role = 'ssc_admin'
            LIMIT 1
        ");
        if (!$stmt) {
            error_log("[validate_contact] Database prepare failed: " . $db->error);
            echo json_encode(['status' => false, 'message' => 'Database error']);
            exit;
        }
        
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            error_log("[validate_contact] SSC Admin not found for: $user_id");
            echo json_encode(['status' => false, 'message' => 'Admin not found']);
            $stmt->close();
            exit;
        }
        
        $admin = $result->fetch_assoc();
        $stmt->close();
        
        $valid = false;
        $error_msg = '';
        
        if ($contact_type === 'email') {
            if (strtolower($contact_value) !== strtolower($admin['email'])) {
                $error_msg = "Please use your registered email: {$admin['email']}";
                error_log("[validate_contact] SSC Admin email mismatch - input: $contact_value, registered: {$admin['email']}");
            } else {
                $valid = true;
                error_log("[validate_contact] SSC Admin email verified");
            }
        } else if ($contact_type === 'phone') {
            $normalized_input = preg_replace('/\D/', '', $contact_value);
            $normalized_registered = preg_replace('/\D/', '', $admin['phone_number']);
            
            if ($normalized_input !== $normalized_registered) {
                $error_msg = "Please use your registered phone number: {$admin['phone_number']}";
                error_log("[validate_contact] SSC Admin phone mismatch - input: $normalized_input, registered: $normalized_registered");
            } else {
                $valid = true;
                error_log("[validate_contact] SSC Admin phone verified");
            }
        }
        
        echo json_encode([
            'status' => $valid,
            'message' => $error_msg ?: 'Verified',
            'type' => 'ssc_admin'
        ]);
    }
} else {
    // Student validation
    $stmt = $db->prepare("
        SELECT email, phone_number FROM student WHERE student_id = ? LIMIT 1
    ");
    
    if (!$stmt) {
        error_log("[validate_contact] Database prepare failed: " . $db->error);
        echo json_encode(['status' => false, 'message' => 'Database error']);
        exit;
    }
    
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("[validate_contact] Student not found for: $user_id");
        echo json_encode(['status' => false, 'message' => 'Student not found']);
        $stmt->close();
        exit;
    }
    
    $student = $result->fetch_assoc();
    $stmt->close();
    
    $valid = false;
    $error_msg = '';
    
    if ($contact_type === 'email') {
        if (strtolower($contact_value) !== strtolower($student['email'])) {
            $error_msg = "Please use your registered G-Suite account: {$student['email']}";
            error_log("[validate_contact] Student email mismatch - input: $contact_value, registered: {$student['email']}");
        } else {
            $valid = true;
            error_log("[validate_contact] Student email verified");
        }
    } else if ($contact_type === 'phone') {
        $normalized_input = preg_replace('/\D/', '', $contact_value);
        $normalized_registered = preg_replace('/\D/', '', $student['phone_number']);
        
        if ($normalized_input !== $normalized_registered) {
            $error_msg = "Please use your registered phone number: {$student['phone_number']}";
            error_log("[validate_contact] Student phone mismatch - input: $normalized_input, registered: $normalized_registered");
        } else {
            $valid = true;
            error_log("[validate_contact] Student phone verified");
        }
    }
    
    echo json_encode([
        'status' => $valid,
        'message' => $error_msg ?: 'Verified',
        'type' => 'student'
    ]);
}
?>