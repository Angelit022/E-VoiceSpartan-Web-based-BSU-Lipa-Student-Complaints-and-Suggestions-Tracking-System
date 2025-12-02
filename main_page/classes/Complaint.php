<?php
class Complaint {
    private $conn;
    private $bannedWords = [
        'fuck', 'shit', 'bitch', 'asshole', 'idiot', 'stupid',
        'offensive', 'inappropriate', 'vulgar'
    ];
    
    private $validCategories = [
        'Academic Concerns',
        'Facilities and Campus Environment',
        'Administrative Services',
        'Student Services and Welfare',
        'Technology and Online Systems',
        'Security and Discipline',
        'Campus Policies and Regulations',
        'Accessibility',
        'Others'
    ];

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function validate(array $data, $student_id) {
        $required = ['category', 'title', 'description', 'priority'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                return ['success' => false, 'message' => ucfirst($field) . " is required."];
            }
        }
        
        if (!in_array($data['category'], $this->validCategories)) {
            return ['success' => false, 'message' => "Invalid category selected."];
        }

        if (mb_strlen($data['title']) > 255) {
            return ['success' => false, 'message' => "Title must be 255 characters or less."];
        }

        if (mb_strlen($data['description']) < 10) {
            return ['success' => false, 'message' => "Description must be at least 10 characters."];
        }
        
        if (!in_array($data['priority'], ['Low', 'Medium', 'High'])) {
            return ['success' => false, 'message' => "Invalid priority level."];
        }

        if (empty($student_id)) {
            return ['success' => false, 'message' => "Student session not found."];
        }

        return ['success' => true, 'message' => 'OK'];
    }

    public function create(array $data, string $student_id = null) {
        if ($student_id === null || trim($student_id) === '') {
            return ['success' => false, 'message' => 'Student ID is required.'];
        }
        $checkSql = "SELECT student_id FROM student WHERE student_id = ?";
        $checkStmt = $this->conn->prepare($checkSql);
        
        if (!$checkStmt) {
            return ['success' => false, 'message' => 'Database prepare error: ' . $this->conn->error];
        }
        
        $checkStmt->bind_param("s", $student_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            $checkStmt->close();
            return ['success' => false, 'message' => 'Student ID not found in database.'];
        }
        $checkStmt->close();

        $category = trim($data['category']);
        $title = $this->filterBannedWords(trim($data['title']));
        $description = $this->filterBannedWords(trim($data['description']));
        $priority = trim($data['priority']);
        $is_anonymous = !empty($data['is_anonymous']) ? 1 : 0;
        
        if (!in_array($category, $this->validCategories)) {
            return ['success' => false, 'message' => 'Invalid category.'];
        }
        
        $statusSql = "SELECT status_id FROM status WHERE status_name = 'Pending' LIMIT 1";
        $statusResult = $this->conn->query($statusSql);
        
        if (!$statusResult || $statusResult->num_rows === 0) {
            return ['success' => false, 'message' => 'Default status "Pending" not found in database.'];
        }
        
        $statusRow = $statusResult->fetch_assoc();
        $status_id = $statusRow['status_id'];

        $sql = "INSERT INTO complaint (student_id, category, title, description, priority, status_id, is_anonymous, date_submitted)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            return ['success' => false, 'message' => "Prepare failed: " . $this->conn->error];
        }

        $stmt->bind_param(
            "sssssii",
            $student_id,
            $category,
            $title,
            $description,
            $priority,
            $status_id,
            $is_anonymous
        );

        if ($stmt->execute()) {
            $insertId = $stmt->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Complaint created', 'complaint_id' => $insertId];
        } else {
            $err = $stmt->error;
            $stmt->close();
            return ['success' => false, 'message' => 'Insert failed: ' . $err];
        }
    }

    public function containsBannedWords(string $text): bool {
        $lowerText = strtolower($text);
        foreach ($this->bannedWords as $word) {
            if (strpos($lowerText, strtolower($word)) !== false) {
                return true;
            }
        }
        return false;
    }

    public function filterBannedWords(string $text): string {
        $lowerText = strtolower($text);
        foreach ($this->bannedWords as $word) {
            $text = preg_replace('/\b' . preg_quote($word, '/') . '\b/i', str_repeat('*', strlen($word)), $text);
        }
        return $text;
    }
    
    public function getValidCategories(): array {
        return $this->validCategories;
    }
}
?>