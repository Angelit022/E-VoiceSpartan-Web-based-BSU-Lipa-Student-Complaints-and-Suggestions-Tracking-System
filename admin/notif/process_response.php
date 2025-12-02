<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Authentication check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Please login']);
    exit();
}

// Authorization check - verify admin role
if (!isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], ['super_admin', 'ssc_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access Denied - Admin privileges required']);
    exit();
}

$action = $_POST['action'] ?? '';

if (empty($action)) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit();
}

try {
    require_once __DIR__ . '/../../db.php';
    require_once __DIR__ . '/../classes/ResponseNotificationHandler.php';
    require_once __DIR__ . '/../classes/AdminActivityLog.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Failed to get database connection");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    if ($action === 'send_response') {
        $result = handleSendResponse($db);
    } elseif ($action === 'update_status') {
        $result = handleUpdateStatus($db);
    } else {
        $result = ['success' => false, 'message' => 'Invalid action'];
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

exit();

function handleSendResponse($db) {
    $submissionId = intval($_POST['submissionId'] ?? 0);
    $submissionType = trim($_POST['submissionType'] ?? '');
    $studentId = trim($_POST['studentId'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if ($submissionId <= 0) {
        return ['success' => false, 'message' => 'Invalid submission ID'];
    }
    
    if (!in_array($submissionType, ['Complaint', 'Suggestion'])) {
        return ['success' => false, 'message' => 'Invalid submission type'];
    }
    
    if (empty($message)) {
        return ['success' => false, 'message' => 'Response message is required'];
    }
    
    // Get admin information
    $adminId = null;
    $adminName = '';
    
    try {
        if (isset($_SESSION['admin_id'])) {
            $sessionAdminId = $_SESSION['admin_id'];
            $stmt = $db->prepare("SELECT admin_id, name FROM admin WHERE admin_id = ? OR email = ?");
            if ($stmt) {
                $stmt->bind_param("is", $sessionAdminId, $sessionAdminId);
                $stmt->execute();
                $adminResult = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                if ($adminResult) {
                    $adminId = $adminResult['admin_id'];
                    $adminName = $adminResult['name'];
                }
            }
        }
        
        if (!$adminId) {
            $adminEmail = $_SESSION['user_id'] ?? $_SESSION['email'] ?? 'admin@system';
            $adminName = $_SESSION['first_name'] ?? 'Administrator';
            
            $result = $db->query("SELECT COALESCE(MAX(admin_id), 0) + 1 as next_id FROM admin");
            $row = $result->fetch_assoc();
            $newAdminId = $row['next_id'];
            
            $stmt = $db->prepare(
                "INSERT INTO admin (admin_id, name, email, password, role, created_at) 
                 VALUES (?, ?, ?, 'admin123', ?, NOW())"
            );
            
            if ($stmt) {
                $role = $_SESSION['admin_role'] ?? 'ssc_admin';
                $stmt->bind_param("isss", $newAdminId, $adminName, $adminEmail, $role);
                
                if ($stmt->execute()) {
                    $adminId = $newAdminId;
                } else {
                    $fallback = $db->query("SELECT admin_id, name FROM admin ORDER BY admin_id LIMIT 1")->fetch_assoc();
                    if ($fallback) {
                        $adminId = $fallback['admin_id'];
                        $adminName = $fallback['name'];
                    }
                }
                $stmt->close();
            }
        }
        
        if (!$adminId) {
            return ['success' => false, 'message' => 'Admin account error'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error retrieving admin information'];
    }
    
    $complaintId = ($submissionType === 'Complaint') ? $submissionId : null;
    $suggestionId = ($submissionType === 'Suggestion') ? $submissionId : null;
    
    try {
        $table = ($submissionType === 'Complaint') ? 'complaint' : 'suggestion';
        $idColumn = ($submissionType === 'Complaint') ? 'complaint_id' : 'suggestion_id';
        
        $stmt = $db->prepare("SELECT status_id, student_id, is_anonymous FROM $table WHERE $idColumn = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $submissionId);
        $stmt->execute();
        $submission = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$submission) {
            return ['success' => false, 'message' => 'Submission not found'];
        }
        
        $isAnonymous = $submission['is_anonymous'] == 1;
        $actualStudentId = $submission['student_id'];
        
        if ($isAnonymous) {
            $studentId = $actualStudentId;
        }
        
        if ($submission['status_id'] != 2) {
            return [
                'success' => false,
                'message' => 'You can only respond to submissions with "In Progress" status.'
            ];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error checking submission status'];
    }
    
    try {
        $stmt = $db->prepare(
            "INSERT INTO response (complaint_id, suggestion_id, admin_id, message, date_responded) 
             VALUES (?, ?, ?, ?, NOW())"
        );
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error preparing response'];
        }
        
        $stmt->bind_param("iiis", $complaintId, $suggestionId, $adminId, $message);
        
        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Error saving response: ' . $stmt->error];
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error saving response: ' . $e->getMessage()];
    }
    
    // LOG ACTIVITY - Response sent
    try {
        $activityLog = new AdminActivityLog($adminId, $adminName);
        if ($submissionType === 'Complaint') {
            $activityLog->logComplaintResponse($submissionId, $studentId, $isAnonymous);
        } else {
            $activityLog->logSuggestionResponse($submissionId, $studentId, $isAnonymous);
        }
    } catch (Exception $e) {
        // Log error but don't fail the operation
        error_log("Failed to log response activity: " . $e->getMessage());
    }
    
    try {
        $handler = new ResponseNotificationHandler();
        $notifResult = $handler->notifyStudentOfResponse(
            $studentId,
            $complaintId,
            $suggestionId,
            $message,
            $adminName
        );
        
        return [
            'success' => true,
            'message' => 'Response sent successfully!' . ($isAnonymous ? ' (Anonymous submission)' : '')
        ];
        
    } catch (Exception $e) {
        return [
            'success' => true,
            'message' => 'Response saved. Notification may have failed.'
        ];
    }
}

function handleUpdateStatus($db) {
    $submissionId = intval($_POST['submissionId'] ?? 0);
    $submissionType = trim($_POST['submissionType'] ?? '');
    $statusId = intval($_POST['statusId'] ?? 0);
    
    if ($submissionId <= 0) {
        return ['success' => false, 'message' => 'Invalid submission ID'];
    }
    
    if (!in_array($submissionType, ['Complaint', 'Suggestion'])) {
        return ['success' => false, 'message' => 'Invalid submission type'];
    }
    
    if (!in_array($statusId, [1, 2, 3, 4])) {
        return ['success' => false, 'message' => 'Invalid status'];
    }
    
    $table = ($submissionType === 'Complaint') ? 'complaint' : 'suggestion';
    $idColumn = ($submissionType === 'Complaint') ? 'complaint_id' : 'suggestion_id';
    
    // Get admin information for logging
    $adminId = $_SESSION['admin_id'] ?? null;
    $adminName = $_SESSION['first_name'] ?? 'Administrator';
    
    if ($adminId) {
        try {
            $stmt = $db->prepare("SELECT name FROM admin WHERE admin_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $adminId);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                if ($result) {
                    $adminName = $result['name'];
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            // Use default admin name if query fails
        }
    }
    
    try {
        $stmt = $db->prepare("SELECT student_id, status_id FROM $table WHERE $idColumn = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $submissionId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$result) {
            return ['success' => false, 'message' => 'Submission not found'];
        }
        
        if ($result['status_id'] == $statusId) {
            return ['success' => false, 'message' => 'Status is already set to this value'];
        }
        
        $studentId = $result['student_id'];
        $oldStatusId = $result['status_id'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error retrieving submission'];
    }
    
    // Get old status name for logging
    $oldStatusName = 'Unknown';
    try {
        $stmt = $db->prepare("SELECT status_name FROM status WHERE status_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $oldStatusId);
            $stmt->execute();
            $statusResult = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $oldStatusName = $statusResult['status_name'] ?? 'Unknown';
        }
    } catch (Exception $e) {
        // Use default if query fails
    }
    
    try {
        $stmt = $db->prepare("UPDATE $table SET status_id = ? WHERE $idColumn = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("ii", $statusId, $submissionId);
        
        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to update status'];
        }
        
        $stmt->close();
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating status'];
    }
    
    $statusName = 'Unknown';
    try {
        $stmt = $db->prepare("SELECT status_name FROM status WHERE status_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $statusId);
            $stmt->execute();
            $statusResult = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $statusName = $statusResult['status_name'] ?? 'Unknown';
        }
    } catch (Exception $e) {
        // Use default if query fails
    }
    
    // LOG ACTIVITY - Status updated
    if ($adminId) {
        try {
            $activityLog = new AdminActivityLog($adminId, $adminName);
            if ($submissionType === 'Complaint') {
                $activityLog->logComplaintStatusUpdate($submissionId, $oldStatusName, $statusName);
            } else {
                $activityLog->logSuggestionStatusUpdate($submissionId, $oldStatusName, $statusName);
            }
        } catch (Exception $e) {
            // Log error but don't fail the operation
            error_log("Failed to log status update activity: " . $e->getMessage());
        }
    }
    
    try {
        $notificationMsg = "Your " . strtolower($submissionType) . " (ID: # 000$submissionId) status has been updated to: $statusName";
        $complaintId = ($submissionType === 'Complaint') ? $submissionId : null;
        $suggestionId = ($submissionType === 'Suggestion') ? $submissionId : null;
        
        $stmt = $db->prepare(
            "INSERT INTO notifications (student_id, message, type, complaint_id, suggestion_id, created_at, is_read) 
             VALUES (?, ?, 'status_update', ?, ?, NOW(), 0)"
        );
        
        if ($stmt) {
            $stmt->bind_param("ssii", $studentId, $notificationMsg, $complaintId, $suggestionId);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        // Notification failed but status updated successfully
    }
    
    return [
        'success' => true,
        'message' => 'Status updated to "' . $statusName . '" successfully!'
    ];
}
?>