<?php
class Suggestion {
    private $conn;
    private $bannedWords = [
        'fuck', 'shit', 'bitch', 'asshole', 'idiot', 'stupid',
        'offensive', 'inappropriate', 'vulgar'
    ];
    

    private $validCategories = [
        'Academic Improvements',
        'Facility Enhancements',
        'Administrative Process Improvements',
        'Student Support and Engagement',
        'Technology and System Upgrades',
        'Campus Safety and Security Improvements',
        'Environmental and Sustainability Initiatives',
        'Mobility Enhancements',
        'Others'
    ];

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function validate(array $data, $student_id) {
        $required = ['category', 'title', 'description'];
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

        if (empty($student_id)) {
            return ['success' => false, 'message' => "Student session not found."];
        }

        if (isset($data['priority']) && !in_array($data['priority'], ['Low', 'Medium', 'High'])) {
            return ['success' => false, 'message' => "Invalid priority level."];
        }

        return ['success' => true, 'message' => 'OK'];
    }

    public function create(array $data, string $student_id = null) {
        $category = trim($data['category']);
        $title = $this->filterBannedWords(trim($data['title']));
        $priority = isset($data['priority']) ? trim($data['priority']) : 'Medium';
        $description = $this->filterBannedWords(trim($data['description']));
        $is_anonymous = !empty($data['is_anonymous']) ? 1 : 0;
        
        $studentVal = $student_id !== null ? $student_id : '';
        if (empty($studentVal)) {
            return ['success' => false, 'message' => 'Student ID is required.'];
        }
        
        if (!in_array($category, $this->validCategories)) {
            return ['success' => false, 'message' => 'Invalid category.'];
        }

        $statusSql = "SELECT status_id FROM status WHERE status_name = 'Pending' LIMIT 1";
        $statusResult = $this->conn->query($statusSql);
        if (!$statusResult || $statusResult->num_rows === 0) {
            return ['success' => false, 'message' => 'Default status "Pending" not found in database. Please contact administrator.'];
        }
        $statusRow = $statusResult->fetch_assoc();
        $status_id = $statusRow['status_id'];

        $sql = "INSERT INTO suggestion (student_id, category, title, priority, description, status_id, is_anonymous)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => "Prepare failed: " . $this->conn->error];
        }

        $stmt->bind_param(
            "sssssii",
            $studentVal,
            $category,
            $title,
            $priority,
            $description,
            $status_id,
            $is_anonymous
        );

        if ($stmt->execute()) {
            $insertId = $stmt->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Suggestion created', 'suggestion_id' => $insertId];
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