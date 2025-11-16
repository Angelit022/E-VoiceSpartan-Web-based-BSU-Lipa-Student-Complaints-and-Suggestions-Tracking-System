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
        if (!$stmt) {
            return null;
        }
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

    /**
     * Get submission details for view
     */
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

    /**
     * Get submission for editing with ownership verification
     */
    public function getSubmissionForEdit($type, $id) {
        $submission = $this->getSubmissionDetails($type, $id);
        
        if (!$submission) {
            return ['success' => false, 'message' => 'Submission not found'];
        }

        if ($submission['student_id'] !== $this->student_id) {
            return ['success' => false, 'message' => 'Unauthorized access'];
        }

        if ($submission['status'] !== 'Pending') {
            return ['success' => false, 'message' => 'Only pending submissions can be edited'];
        }

        return ['success' => true, 'data' => $submission];
    }

    /**
     * Update submission
     */
    public function updateSubmission($type, $id, $data) {
        // Verify ownership and status
        $check = $this->getSubmissionForEdit($type, $id);
        if (!$check['success']) {
            return $check;
        }

        if ($type === 'Complaint') {
            $query = "UPDATE complaint 
                     SET title = ?, description = ?, category = ?, priority = ? 
                     WHERE complaint_id = ? AND student_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssssss", 
                $data['title'], 
                $data['description'], 
                $data['category'], 
                $data['priority'], 
                $id, 
                $this->student_id
            );
        } else {
            $query = "UPDATE suggestion 
                     SET title = ?, description = ?, category = ?, priority = ? 
                     WHERE suggestion_id = ? AND student_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssssss", 
                $data['title'], 
                $data['description'], 
                $data['category'],
                $data['priority'],
                $id, 
                $this->student_id
            );
        }
        
        $success = $stmt->execute();
        $stmt->close();
        
        return [
            'success' => $success,
            'message' => $success ? 'Submission updated successfully' : 'Failed to update submission'
        ];
    }

    /**
     * Delete submission (only if pending)
     */
    public function deleteSubmission($type, $id) {
        // Get submission with status
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

        // Get attachment paths for complaints before deletion
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

        // Delete submission (CASCADE handles related records)
        if ($type === 'Complaint') {
            $delete_query = "DELETE FROM complaint WHERE complaint_id = ? AND student_id = ?";
        } else {
            $delete_query = "DELETE FROM suggestion WHERE suggestion_id = ? AND student_id = ?";
        }

        $delete_stmt = $this->db->prepare($delete_query);
        $delete_stmt->bind_param("is", $id, $this->student_id);
        $success = $delete_stmt->execute();
        $delete_stmt->close();

        // Delete physical files
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

    /**
     * Get attachments for a complaint
     */
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