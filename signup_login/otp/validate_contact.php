<?php
/**
 * validate_contact.php - Validate if the requested email/phone matches user's registered info
 * This prevents unauthorized OTP requests
 * Updated: Added masking for security
 */
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../classes/AdminAuthService.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Invalid request']);
    exit;
}

/**
 * Mask phone number for security (show first 2 and last 2 digits)
 * Example: 09466161074 -> 09*******74
 */
function maskPhoneNumber($phone) {
    $phone = preg_replace('/\D/', '', $phone); // Remove non-digits
    if (strlen($phone) < 4) {
        return str_repeat('*', strlen($phone));
    }
    $first = substr($phone, 0, 2);
    $last = substr($phone, -2);
    $middle = str_repeat('*', strlen($phone) - 4);
    return $first . $middle . $last;
}

/**
 * Mask email for security (show first 2 chars of username and domain)
 * Example: john.doe@gmail.com -> jo*****@gm***.com
 * Example: 23-36439@g.batstate-u.edu.ph -> 23*****@g.*******.edu.ph
 */
function maskEmail($email) {
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return str_repeat('*', strlen($email));
    }
    
    $username = $parts[0];
    $domain = $parts[1];
    
    // Mask username (show first 2 characters)
    if (strlen($username) <= 2) {
        $maskedUsername = $username;
    } else {
        $maskedUsername = substr($username, 0, 2) . str_repeat('*', min(5, strlen($username) - 2));
    }
    
    // Mask domain (show first 2 characters before first dot and keep TLD)
    $domainParts = explode('.', $domain);
    if (count($domainParts) > 0) {
        $firstPart = $domainParts[0];
        if (strlen($firstPart) <= 2) {
            $maskedDomain = $firstPart;
        } else {
            $maskedDomain = substr($firstPart, 0, 2) . str_repeat('*', min(3, strlen($firstPart) - 2));
        }
        
        // Keep the rest of domain structure
        for ($i = 1; $i < count($domainParts); $i++) {
            if ($i == count($domainParts) - 1) {
                // Keep TLD visible
                $maskedDomain .= '.' . $domainParts[$i];
            } else {
                // Mask middle parts
                $maskedDomain .= '.' . str_repeat('*', strlen($domainParts[$i]));
            }
        }
    } else {
        $maskedDomain = $domain;
    }
    
    return $maskedUsername . '@' . $maskedDomain;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$contact_type = $input['contact_type'] ?? ''; // 'email' or 'phone'
$contact_value = trim($input['contact_value'] ?? '');
$user_id = $_SESSION['authUser'] ?? '';
$is_admin = $_SESSION['is_admin'] ?? false;

if (empty($contact_type) || empty($contact_value) || empty($user_id)) {
    echo json_encode(['status' => false, 'message' => 'Missing required fields']);
    exit;
}

$db = (new Database())->getConnection();

if (!$db) {
    echo json_encode(['status' => false, 'message' => 'Database error']);
    exit;
}

if ($is_admin) {
    // Admin validation - Only Super Admin and SSC Admin
    $admin_type = $_SESSION['admin_type'] ?? '';
    
    if ($admin_type === 'super_admin') {
        // Super Admin must use their specific email/phone
        $adminAuth = new AdminAuthService();
        $contact_info = $adminAuth->getAdminContactInfo($user_id);
        
        if (!$contact_info) {
            echo json_encode(['status' => false, 'message' => 'Admin not found']);
            exit;
        }
        
        $valid = false;
        $error_msg = '';
        
        if ($contact_type === 'email') {
            if (strtolower($contact_value) !== strtolower($contact_info['email'])) {
                $maskedEmail = maskEmail($contact_info['email']);
                $error_msg = "As Super Admin, please use your registered email: {$maskedEmail}";
            } else {
                $valid = true;
            }
        } else if ($contact_type === 'phone') {
            $normalized_input = preg_replace('/\D/', '', $contact_value);
            $normalized_registered = preg_replace('/\D/', '', $contact_info['phone']);
            
            if ($normalized_input !== $normalized_registered) {
                $maskedPhone = maskPhoneNumber($contact_info['phone']);
                $error_msg = "As Super Admin, please use your registered phone number: {$maskedPhone}";
            } else {
                $valid = true;
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
            echo json_encode(['status' => false, 'message' => 'Database error']);
            exit;
        }
        
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
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
                $maskedEmail = maskEmail($admin['email']);
                $error_msg = "Please use your registered email: {$maskedEmail}";
            } else {
                $valid = true;
            }
        } else if ($contact_type === 'phone') {
            $normalized_input = preg_replace('/\D/', '', $contact_value);
            $normalized_registered = preg_replace('/\D/', '', $admin['phone_number']);
            
            if ($normalized_input !== $normalized_registered) {
                $maskedPhone = maskPhoneNumber($admin['phone_number']);
                $error_msg = "Please use your registered phone number: {$maskedPhone}";
            } else {
                $valid = true;
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
        echo json_encode(['status' => false, 'message' => 'Database error']);
        exit;
    }
    
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
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
            $maskedEmail = maskEmail($student['email']);
            $error_msg = "Please use your registered G-Suite account: {$maskedEmail}";
        } else {
            $valid = true;
        }
    } else if ($contact_type === 'phone') {
        $normalized_input = preg_replace('/\D/', '', $contact_value);
        $normalized_registered = preg_replace('/\D/', '', $student['phone_number']);
        
        if ($normalized_input !== $normalized_registered) {
            $maskedPhone = maskPhoneNumber($student['phone_number']);
            $error_msg = "Please use your registered phone number: {$maskedPhone}";
        } else {
            $valid = true;
        }
    }
    
    echo json_encode([
        'status' => $valid,
        'message' => $error_msg ?: 'Verified',
        'type' => 'student'
    ]);
}
?>