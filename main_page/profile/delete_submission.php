<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

require_once '../../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';
$id = intval($data['id'] ?? 0);

if (empty($type) || empty($id)) {
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Invalid request parameters']));
}

$database = new Database();
$conn = $database->getConnection();
$user_id = $_SESSION['user_id'];


if ($type === 'Complaint') {
    $query = "SELECT c.student_id, s.status_name 
              FROM complaint c 
              LEFT JOIN status s ON c.status_id = s.status_id 
              WHERE c.complaint_id = ?";
} else {
    $query = "SELECT s.student_id, st.status_name 
              FROM suggestion s 
              LEFT JOIN status st ON s.status_id = st.status_id 
              WHERE s.suggestion_id = ?";
}

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$submission = $result->fetch_assoc();
$stmt->close();

if (!$submission) {
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Submission not found']));
}

if ($submission['student_id'] !== $user_id) {
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

if ($submission['status_name'] !== 'Pending') {
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Only pending submissions can be deleted']));
}

if ($type === 'Complaint') {
    // Get attachment file paths
    $attach_query = "SELECT file_path FROM attachment WHERE complaint_id = ?";
    $attach_stmt = $conn->prepare($attach_query);
    $attach_stmt->bind_param("i", $id);
    $attach_stmt->execute();
    $attach_result = $attach_stmt->get_result();
    
    $filePaths = [];
    while ($row = $attach_result->fetch_assoc()) {
        $filePaths[] = $row['file_path'];
    }
    $attach_stmt->close();
    
    // Delete files from filesystem
    foreach ($filePaths as $filePath) {
        $fullPath = __DIR__ . '/../../' . $filePath;
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }
}

// Delete the submission (CASCADE will handle attachments, feedback, responses)
if ($type === 'Complaint') {
    $delete_query = "DELETE FROM complaint WHERE complaint_id = ? AND student_id = ?";
} else {
    $delete_query = "DELETE FROM suggestion WHERE suggestion_id = ? AND student_id = ?";
}

$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("is", $id, $user_id);

if ($delete_stmt->execute()) {
    $delete_stmt->close();
    header('Content-Type: application/json');
    exit(json_encode(['success' => true, 'message' => ucfirst(strtolower($type)) . ' deleted successfully']));
} else {
    $delete_stmt->close();
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Failed to delete ' . strtolower($type)]));
}
?>