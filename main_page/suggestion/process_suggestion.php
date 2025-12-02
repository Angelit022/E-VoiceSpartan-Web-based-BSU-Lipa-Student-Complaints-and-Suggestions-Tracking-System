<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User session not found. Please log in again.']);
    exit();
}

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../classes/Suggestion.php';
require_once __DIR__ . '/../classes/StudentActivityLog.php';

try {
    $database = new Database();
    $mysqli = $database->getConnection();
    
    if (!$mysqli) {
        throw new Exception('Database connection failed');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]);
    exit();
}

$student_id = $_SESSION['user_id'];

if (empty($student_id)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Student ID not found in session.']);
    exit();
}

$activityLog = new StudentActivityLog($student_id);

$category = isset($_POST['area']) ? trim($_POST['area']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$priority = isset($_POST['priority']) ? trim($_POST['priority']) : 'Medium';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$is_anonymous = (isset($_POST['anonymous']) && $_POST['anonymous'] === '1') ? 1 : 0;

if (!in_array($priority, ['Low', 'Medium', 'High'])) {
    $priority = 'Medium';
}

$data = [
    'category' => $category,
    'title' => $title,
    'priority' => $priority,
    'description' => $description,
    'is_anonymous' => $is_anonymous
];

$suggestion = new Suggestion($mysqli);

$valid = $suggestion->validate($data, $student_id);
if (!$valid['success']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $valid['message']]);
    exit();
}

$result = $suggestion->create($data, $student_id);
if ($result['success']) {
    $suggestion_id = $result['suggestion_id'];
    
    $activityLog->logSuggestionCreate($suggestion_id, $is_anonymous);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'suggestion_id' => $suggestion_id,
        'is_anonymous' => (bool)$is_anonymous,
        'message' => 'Suggestion submitted successfully.'
    ]);
    exit();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $result['message']]);
    exit();
}
?>