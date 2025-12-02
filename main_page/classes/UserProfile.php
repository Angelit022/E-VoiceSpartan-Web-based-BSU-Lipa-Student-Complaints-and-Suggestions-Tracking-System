<?php
class UserProfile {
    private $db;
    private $student_id;

    public function __construct($database, $student_id) {
        $this->db = $database;
        $this->student_id = $student_id;
    }

    public function getUserInfo() {
        $query = "SELECT student_id, first_name, last_name, email, phone_number FROM student WHERE student_id = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) return null;
        
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    public function getAllSubmissions() {
        $complaints = $this->getComplaints();
        $suggestions = $this->getSuggestions();
        
        $all = array_merge($complaints, $suggestions);
        usort($all, function($a, $b) {
            return strtotime($b['date_submitted']) - strtotime($a['date_submitted']);
        });
        
        return $all;
    }

    public function getComplaints() {
        $query = "SELECT 
                    c.complaint_id as id,
                    c.title,
                    c.description,
                    c.category,
                    c.priority,
                    c.date_submitted,
                    s.status_name as status,
                    'Complaint' as type,
                    c.is_anonymous
                  FROM complaint c
                  LEFT JOIN status s ON c.status_id = s.status_id
                  WHERE c.student_id = ?
                  ORDER BY c.date_submitted DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $complaints = [];
        while ($row = $result->fetch_assoc()) {
            $complaints[] = $row;
        }
        $stmt->close();
        return $complaints;
    }

    public function getSuggestions() {
        $query = "SELECT 
                    s.suggestion_id as id,
                    s.title,
                    s.description,
                    s.category,
                    s.priority,
                    s.date_submitted,
                    st.status_name as status,
                    'Suggestion' as type,
                    s.is_anonymous
                  FROM suggestion s
                  LEFT JOIN status st ON s.status_id = st.status_id
                  WHERE s.student_id = ?
                  ORDER BY s.date_submitted DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $suggestions = [];
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = $row;
        }
        $stmt->close();
        return $suggestions;
    }

    public function getStatistics() {
        $stats = [];

        $query = "SELECT COUNT(*) as count FROM complaint WHERE student_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $stats['total_complaints'] = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        $query = "SELECT COUNT(*) as count FROM suggestion WHERE student_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $stats['total_suggestions'] = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        $query = "SELECT COUNT(*) as count FROM complaint WHERE student_id = ? AND status_id = 3";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $resolved = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        
        $query = "SELECT COUNT(*) as count FROM suggestion WHERE student_id = ? AND status_id = 3";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $resolved += $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        
        $stats['resolved'] = $resolved;

        $query = "SELECT COUNT(*) as count FROM complaint WHERE student_id = ? AND status_id = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $pending = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        
        $query = "SELECT COUNT(*) as count FROM suggestion WHERE student_id = ? AND status_id = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $pending += $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        
        $stats['pending'] = $pending;

        $query = "SELECT COUNT(*) as count FROM complaint WHERE student_id = ? AND status_id = 2";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $in_progress = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        
        $query = "SELECT COUNT(*) as count FROM suggestion WHERE student_id = ? AND status_id = 2";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $in_progress += $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        
        $stats['in_progress'] = $in_progress;

        $query = "SELECT COUNT(*) as count FROM complaint WHERE student_id = ? AND status_id = 4";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $rejected = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        
        $query = "SELECT COUNT(*) as count FROM suggestion WHERE student_id = ? AND status_id = 4";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $rejected += $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        
        $stats['rejected'] = $rejected;

        return $stats;
    }

    public function getSubmissionDetails($type, $id) {
        if ($type === 'Complaint') {
            $query = "SELECT 
                        c.complaint_id as id,
                        c.title,
                        c.description,
                        c.category,
                        c.priority,
                        c.date_submitted,
                        c.student_id,
                        s.status_name as status,
                        c.is_anonymous,
                        'Complaint' as type
                      FROM complaint c
                      LEFT JOIN status s ON c.status_id = s.status_id
                      WHERE c.complaint_id = ?";
        } else {
            $query = "SELECT 
                        s.suggestion_id as id,
                        s.title,
                        s.description,
                        s.category,
                        s.priority,
                        s.date_submitted,
                        s.student_id,
                        st.status_name as status,
                        s.is_anonymous,
                        'Suggestion' as type
                      FROM suggestion s
                      LEFT JOIN status st ON s.status_id = st.status_id
                      WHERE s.suggestion_id = ?";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

public function getSubmissionForEdit($type, $id) {
    if ($type === 'Complaint') {
        $query = "SELECT c.complaint_id, c.student_id, c.title, c.description, c.category, c.priority, c.is_anonymous, s.status_name 
                 FROM complaint c
                 LEFT JOIN status s ON c.status_id = s.status_id
                 WHERE c.complaint_id = ? AND c.student_id = ?";
    } else if ($type === 'Suggestion') {
        $query = "SELECT su.suggestion_id, su.student_id, su.title, su.description, su.category, su.priority, su.is_anonymous, s.status_name 
                 FROM suggestion su
                 LEFT JOIN status s ON su.status_id = s.status_id
                 WHERE su.suggestion_id = ? AND su.student_id = ?";
    } else {
        return ['success' => false, 'message' => 'Invalid submission type'];
    }

    $stmt = $this->db->prepare($query);
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error'];
    }

    $stmt->bind_param("is", $id, $this->student_id);
    
    if (!$stmt->execute()) {
        $stmt->close();
        return ['success' => false, 'message' => 'Database error'];
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Submission not found or not authorized'];
    }

    $submission = $result->fetch_assoc();
    $stmt->close();

    if ($submission['status_name'] !== 'Pending') {
        return ['success' => false, 'message' => 'Only submissions with Pending status can be edited'];
    }

    return ['success' => true, 'data' => $submission];
}

public function updateSubmission($type, $id, $data) {
        // Verify ownership and status
        $check = $this->getSubmissionForEdit($type, $id);
        if (!$check['success']) {
            return $check;
        }

        if ($type === 'Complaint') {
            $query = "UPDATE complaint 
                     SET title = ?, description = ?, category = ?, priority = ?, is_anonymous = ? 
                     WHERE complaint_id = ? AND student_id = ?";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                return ['success' => false, 'message' => 'Database error'];
            }
            
            $stmt->bind_param("ssssiis", 
                $data['title'], 
                $data['description'], 
                $data['category'], 
                $data['priority'],
                $data['is_anonymous'],
                $id, 
                $this->student_id
            );
            
        } else if ($type === 'Suggestion') {
            $query = "UPDATE suggestion 
                     SET title = ?, description = ?, category = ?, priority = ?, is_anonymous = ? 
                     WHERE suggestion_id = ? AND student_id = ?";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                return ['success' => false, 'message' => 'Database error'];
            }
            
            $stmt->bind_param("ssssiis", 
                $data['title'], 
                $data['description'], 
                $data['category'],
                $data['priority'],
                $data['is_anonymous'],
                $id, 
                $this->student_id
            );
            
        } else {
            return ['success' => false, 'message' => 'Invalid submission type'];
        }
        
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to update submission'];
        }
        
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        if ($affectedRows === 0) {
            return ['success' => false, 'message' => 'Submission not found or not authorized to update'];
        }
        
        return ['success' => true, 'message' => 'Submission updated successfully'];
    }
    
    public function deleteSubmission($type, $id) {
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

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $submission = $result->fetch_assoc();
        $stmt->close();

        if (!$submission) {
            return ['success' => false, 'message' => 'Submission not found'];
        }

        if ($submission['student_id'] !== $this->student_id) {
            return ['success' => false, 'message' => 'Unauthorized access'];
        }

        if ($submission['status_name'] !== 'Pending') {
            return ['success' => false, 'message' => 'Only pending submissions can be deleted'];
        }

        $filePaths = [];
        if ($type === 'Complaint') {
            $attach_query = "SELECT file_path FROM attachment WHERE complaint_id = ?";
            $attach_stmt = $this->db->prepare($attach_query);
            $attach_stmt->bind_param("i", $id);
            $attach_stmt->execute();
            $attach_result = $attach_stmt->get_result();
            
            while ($row = $attach_result->fetch_assoc()) {
                $filePaths[] = $row['file_path'];
            }
            $attach_stmt->close();
        }

        if ($type === 'Complaint') {
            $delete_query = "DELETE FROM complaint WHERE complaint_id = ? AND student_id = ?";
        } else {
            $delete_query = "DELETE FROM suggestion WHERE suggestion_id = ? AND student_id = ?";
        }

        $delete_stmt = $this->db->prepare($delete_query);
        $delete_stmt->bind_param("is", $id, $this->student_id);
        $success = $delete_stmt->execute();
        $delete_stmt->close();

        if ($success && !empty($filePaths)) {
            foreach ($filePaths as $filePath) {
                $fullPath = __DIR__ . '/../../' . $filePath;
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }

        return [
            'success' => $success,
            'message' => $success ? ucfirst(strtolower($type)) . ' deleted successfully' : 'Failed to delete ' . strtolower($type)
        ];
    }

    public function getAttachments($complaint_id) {
        $query = "SELECT attachment_id, file_path, file_type, uploaded_at 
                 FROM attachment 
                 WHERE complaint_id = ? 
                 ORDER BY uploaded_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $complaint_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $attachments = [];
        while ($row = $result->fetch_assoc()) {
            $attachments[] = $row;
        }
        $stmt->close();
        
        return $attachments;
    }

    // Response Methods
    
    public function getResponses($type, $id) {
        $verifyQuery = $type === 'complaint' 
            ? "SELECT student_id FROM complaint WHERE complaint_id = ?"
            : "SELECT student_id FROM suggestion WHERE suggestion_id = ?";
        
        $verifyStmt = $this->db->prepare($verifyQuery);
        $verifyStmt->bind_param("i", $id);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        $record = $verifyResult->fetch_assoc();
        $verifyStmt->close();

        if (!$record || $record['student_id'] !== $this->student_id) {
            return ['success' => false, 'message' => 'Access denied'];
        }

        $idColumn = $type === 'complaint' ? 'complaint_id' : 'suggestion_id';
        $query = "SELECT r.response_id, r.message, r.date_responded, a.name as admin_name, a.role as admin_role
                  FROM response r
                  INNER JOIN admin a ON r.admin_id = a.admin_id
                  WHERE r.$idColumn = ?
                  ORDER BY r.date_responded DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $responses = [];
        while ($row = $result->fetch_assoc()) {
            $responses[] = $row;
        }
        $stmt->close();

        return ['success' => true, 'responses' => $responses];
    }

    public function checkResponseStatus() {
        $checkTableQuery = "CREATE TABLE IF NOT EXISTS response_views (
            view_id INT AUTO_INCREMENT PRIMARY KEY,
            student_id VARCHAR(64) NOT NULL,
            complaint_id INT NULL,
            suggestion_id INT NULL,
            viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_view (student_id, complaint_id, suggestion_id),
            KEY idx_viewed_at (viewed_at),
            FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE
        )";
        $this->db->query($checkTableQuery);

        $query = "
            SELECT 
                'complaint' as type,
                c.complaint_id as id,
                CASE WHEN COUNT(r.response_id) > 0 THEN 1 ELSE 0 END as has_responses,
                CASE WHEN rv.view_id IS NOT NULL THEN 1 ELSE 0 END as has_viewed
            FROM complaint c
            LEFT JOIN response r ON c.complaint_id = r.complaint_id
            LEFT JOIN response_views rv ON c.complaint_id = rv.complaint_id AND rv.student_id = ?
            WHERE c.student_id = ? AND c.status_id IN (2, 3, 4)
            GROUP BY c.complaint_id
            
            UNION ALL
            
            SELECT 
                'suggestion' as type,
                s.suggestion_id as id,
                CASE WHEN COUNT(r.response_id) > 0 THEN 1 ELSE 0 END as has_responses,
                CASE WHEN rv.view_id IS NOT NULL THEN 1 ELSE 0 END as has_viewed
            FROM suggestion s
            LEFT JOIN response r ON s.suggestion_id = r.suggestion_id
            LEFT JOIN response_views rv ON s.suggestion_id = rv.suggestion_id AND rv.student_id = ?
            WHERE s.student_id = ? AND s.status_id IN (2, 3, 4)
            GROUP BY s.suggestion_id
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssss", $this->student_id, $this->student_id, $this->student_id, $this->student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $statuses = [];
        while ($row = $result->fetch_assoc()) {
            $key = $row['type'] . '_' . $row['id'];
            $statuses[$key] = [
                'has_responses' => (int)$row['has_responses'],
                'has_viewed' => (int)$row['has_viewed']
            ];
        }

        $stmt->close();
        return ['success' => true, 'statuses' => $statuses];
    }

    public function markResponseViewed($type, $id) {
        $table = $type === 'complaint' ? 'complaint' : 'suggestion';
        $idColumn = $type === 'complaint' ? 'complaint_id' : 'suggestion_id';
        
        $verifyQuery = "SELECT student_id FROM $table WHERE $idColumn = ?";
        $verifyStmt = $this->db->prepare($verifyQuery);
        $verifyStmt->bind_param("i", $id);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        $record = $verifyResult->fetch_assoc();
        $verifyStmt->close();

        if (!$record || $record['student_id'] !== $this->student_id) {
            return ['success' => false, 'message' => 'Access denied'];
        }

        $checkTableQuery = "CREATE TABLE IF NOT EXISTS response_views (
            view_id INT AUTO_INCREMENT PRIMARY KEY,
            student_id VARCHAR(64) NOT NULL,
            complaint_id INT NULL,
            suggestion_id INT NULL,
            viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_view (student_id, complaint_id, suggestion_id),
            KEY idx_viewed_at (viewed_at),
            FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE
        )";
        $this->db->query($checkTableQuery);

        if ($type === 'complaint') {
            $insertQuery = "INSERT INTO response_views (student_id, complaint_id, viewed_at) 
                           VALUES (?, ?, NOW()) 
                           ON DUPLICATE KEY UPDATE viewed_at = NOW()";
            $insertStmt = $this->db->prepare($insertQuery);
            $insertStmt->bind_param("si", $this->student_id, $id);
        } else {
            $insertQuery = "INSERT INTO response_views (student_id, suggestion_id, viewed_at) 
                           VALUES (?, ?, NOW()) 
                           ON DUPLICATE KEY UPDATE viewed_at = NOW()";
            $insertStmt = $this->db->prepare($insertQuery);
            $insertStmt->bind_param("si", $this->student_id, $id);
        }

        $success = $insertStmt->execute();
        $insertStmt->close();

        return ['success' => $success];
    }

    // Feedback Methods
    
    public function checkFeedbackStatus($type, $id) {
        $table = $type === 'complaint' ? 'complaint' : 'suggestion';
        $idColumn = $type === 'complaint' ? 'complaint_id' : 'suggestion_id';
        
        $verifyQuery = "SELECT student_id FROM $table WHERE $idColumn = ?";
        $verifyStmt = $this->db->prepare($verifyQuery);
        $verifyStmt->bind_param("i", $id);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        $record = $verifyResult->fetch_assoc();
        $verifyStmt->close();

        if (!$record || $record['student_id'] !== $this->student_id) {
            return ['success' => false, 'message' => 'Access denied'];
        }

        $checkQuery = "SELECT rating FROM feedback WHERE $idColumn = ? AND student_id = ?";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bind_param("is", $id, $this->student_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $feedback = $checkResult->fetch_assoc();
        $checkStmt->close();

        if ($feedback) {
            return [
                'success' => true,
                'has_feedback' => true,
                'rating' => $feedback['rating']
            ];
        } else {
            return [
                'success' => true,
                'has_feedback' => false
            ];
        }
    }

    public function checkAllFeedbackStatus() {
        $query = "
            SELECT 
                'complaint' as type,
                c.complaint_id as id,
                CASE WHEN f.feedback_id IS NOT NULL THEN 1 ELSE 0 END as has_feedback
            FROM complaint c
            LEFT JOIN feedback f ON c.complaint_id = f.complaint_id AND f.student_id = ?
            WHERE c.student_id = ? AND c.status_id = 3
            
            UNION ALL
            
            SELECT 
                'suggestion' as type,
                s.suggestion_id as id,
                CASE WHEN f.feedback_id IS NOT NULL THEN 1 ELSE 0 END as has_feedback
            FROM suggestion s
            LEFT JOIN feedback f ON s.suggestion_id = f.suggestion_id AND f.student_id = ?
            WHERE s.student_id = ? AND s.status_id = 3
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssss", $this->student_id, $this->student_id, $this->student_id, $this->student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $statuses = [];
        while ($row = $result->fetch_assoc()) {
            $key = $row['type'] . '_' . $row['id'];
            $statuses[$key] = (int)$row['has_feedback'];
        }

        $stmt->close();
        return ['success' => true, 'statuses' => $statuses];
    }

    public function submitFeedback($type, $id, $rating) {
        $table = $type === 'complaint' ? 'complaint' : 'suggestion';
        $idColumn = $type === 'complaint' ? 'complaint_id' : 'suggestion_id';
        
        $verifyQuery = "SELECT c.student_id, s.status_name 
                        FROM $table c
                        LEFT JOIN status s ON c.status_id = s.status_id
                        WHERE c.$idColumn = ?";
        $verifyStmt = $this->db->prepare($verifyQuery);
        $verifyStmt->bind_param("i", $id);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        $record = $verifyResult->fetch_assoc();
        $verifyStmt->close();

        if (!$record || $record['student_id'] !== $this->student_id) {
            return ['success' => false, 'message' => 'Access denied'];
        }

        if ($record['status_name'] !== 'Resolved') {
            return ['success' => false, 'message' => 'Can only provide feedback for resolved submissions'];
        }

        $checkQuery = "SELECT feedback_id FROM feedback WHERE $idColumn = ? AND student_id = ?";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bind_param("is", $id, $this->student_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $existing = $checkResult->fetch_assoc();
        $checkStmt->close();

        if ($existing) {
            return ['success' => false, 'message' => 'You have already submitted feedback for this submission'];
        }

        if ($type === 'complaint') {
            $insertQuery = "INSERT INTO feedback (complaint_id, student_id, rating, date_given) VALUES (?, ?, ?, NOW())";
        } else {
            $insertQuery = "INSERT INTO feedback (suggestion_id, student_id, rating, date_given) VALUES (?, ?, ?, NOW())";
        }

        $insertStmt = $this->db->prepare($insertQuery);
        $insertStmt->bind_param("isi", $id, $this->student_id, $rating);
        $success = $insertStmt->execute();
        $insertStmt->close();

        if ($success) {
            return ['success' => true, 'message' => 'Feedback submitted successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to submit feedback'];
        }
    }

    // Static Helper Methods
    
    public static function formatDate($dateString) {
        return date('M d, Y', strtotime($dateString));
    }

    public static function getStatusBadgeClass($status) {
        return match(strtolower(str_replace(' ', '-', $status))) {
            'pending' => 'bg-warning text-dark',
            'in-progress' => 'bg-info text-white',
            'resolved' => 'bg-success',
            'rejected' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public static function getPriorityBadgeClass($priority) {
        return match($priority) {
            'High' => 'bg-danger',
            'Medium' => 'bg-warning text-dark',
            'Low' => 'bg-info',
            default => 'bg-secondary'
        };
    }
}
?>