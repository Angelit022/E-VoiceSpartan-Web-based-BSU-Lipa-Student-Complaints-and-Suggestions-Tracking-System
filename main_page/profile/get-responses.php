<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../db.php';

$id = intval($_GET['id'] ?? 0);
$type = $_GET['type'] ?? '';

if (empty($id) || empty($type)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Determine which table to query
if ($type === 'complaint') {
    $idColumn = 'complaint_id';
    $table = 'complaint';
} elseif ($type === 'suggestion') {
    $idColumn = 'suggestion_id';
    $table = 'suggestion';
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
    exit();
}

// Verify ownership
$verifyQuery = "SELECT student_id FROM $table WHERE $idColumn = ?";
$verifyStmt = $conn->prepare($verifyQuery);
$verifyStmt->bind_param("i", $id);
$verifyStmt->execute();
$verifyResult = $verifyStmt->get_result();
$record = $verifyResult->fetch_assoc();
$verifyStmt->close();

if (!$record || $record['student_id'] !== $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Get responses
$query = "SELECT r.response_id, r.message, r.date_responded, a.name as admin_name, a.role as admin_role
          FROM response r
          INNER JOIN admin a ON r.admin_id = a.admin_id
          WHERE r.$idColumn = ?
          ORDER BY r.date_responded DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$responses = [];
while ($row = $result->fetch_assoc()) {
    $responses[] = $row;
}
$stmt->close();

echo json_encode([
    'success' => true,
    'responses' => $responses
]);
exit();
?>