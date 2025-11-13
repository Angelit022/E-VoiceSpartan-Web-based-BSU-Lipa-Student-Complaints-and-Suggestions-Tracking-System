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
        return $result->fetch_assoc();
    }

 
    public function getAllSubmissions() {
        $complaints = $this->getComplaints();
        $suggestions = $this->getSuggestions();
        
        // Merge and sort by date
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
        return $complaints;
    }

    public function getSuggestions() {
        $query = "SELECT 
                    s.suggestion_id as id,
                    s.title,
                    s.description,
                    s.category,
                    NULL as priority,
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
        return $suggestions;
    }

    public function getStatistics() {
        $stats = [];

        $query = "SELECT COUNT(*) as count FROM complaint WHERE student_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $stats['total_complaints'] = $stmt->get_result()->fetch_assoc()['count'];

        $query = "SELECT COUNT(*) as count FROM suggestion WHERE student_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $stats['total_suggestions'] = $stmt->get_result()->fetch_assoc()['count'];

        $query = "SELECT COUNT(*) as count FROM complaint WHERE student_id = ? AND status_id = 3";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $resolved = $stmt->get_result()->fetch_assoc()['count'];
        
        $query = "SELECT COUNT(*) as count FROM suggestion WHERE student_id = ? AND status_id = 3";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $resolved += $stmt->get_result()->fetch_assoc()['count'];
        
        $stats['resolved'] = $resolved;

        $query = "SELECT COUNT(*) as count FROM complaint WHERE student_id = ? AND status_id = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $pending = $stmt->get_result()->fetch_assoc()['count'];
        
        $query = "SELECT COUNT(*) as count FROM suggestion WHERE student_id = ? AND status_id = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $pending += $stmt->get_result()->fetch_assoc()['count'];
        
        $stats['pending'] = $pending;

        $query = "SELECT COUNT(*) as count FROM complaint WHERE student_id = ? AND status_id = 2";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $in_progress = $stmt->get_result()->fetch_assoc()['count'];
        
        $query = "SELECT COUNT(*) as count FROM suggestion WHERE student_id = ? AND status_id = 2";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $in_progress += $stmt->get_result()->fetch_assoc()['count'];
        
        $stats['in_progress'] = $in_progress;

        $query = "SELECT COUNT(*) as count FROM complaint WHERE student_id = ? AND status_id = 4";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $rejected = $stmt->get_result()->fetch_assoc()['count'];
        
        $query = "SELECT COUNT(*) as count FROM suggestion WHERE student_id = ? AND status_id = 4";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $this->student_id);
        $stmt->execute();
        $rejected += $stmt->get_result()->fetch_assoc()['count'];
        
        $stats['rejected'] = $rejected;

        return $stats;
    }

    
    // Get single submission details
    public function getSubmissionDetails($type, $id) {
        if ($type === 'Complaint') {
            $query = "SELECT 
                        c.complaint_id as id,
                        c.title,
                        c.description,
                        c.category,
                        c.priority,
                        c.date_submitted,
                        c.location,
                        s.status_name as status
                      FROM complaint c
                      LEFT JOIN status s ON c.status_id = s.status_id
                      WHERE c.complaint_id = ? AND c.student_id = ?";
        } else {
            $query = "SELECT 
                        s.suggestion_id as id,
                        s.title,
                        s.description,
                        s.category,
                        NULL as priority,
                        s.date_submitted,
                        NULL as location,
                        st.status_name as status
                      FROM suggestion s
                      LEFT JOIN status st ON s.status_id = st.status_id
                      WHERE s.suggestion_id = ? AND s.student_id = ?";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("is", $id, $this->student_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    //Update submission (for edit functionality)
    public function updateSubmission($type, $id, $title, $description, $category = null, $priority = null) {
        if ($type === 'Complaint') {
            $query = "UPDATE complaint SET title = ?, description = ?, category = ?, priority = ? 
                     WHERE complaint_id = ? AND student_id = ? AND status_id = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssssis", $title, $description, $category, $priority, $id, $this->student_id);
        } else {
            $query = "UPDATE suggestion SET title = ?, description = ?, category = ? 
                     WHERE suggestion_id = ? AND student_id = ? AND status_id = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssss", $title, $description, $category, $id, $this->student_id);
        }
        
        return $stmt->execute();
    }

     //Format date for display
    public static function formatDate($dateString) {
        return date('M d, Y', strtotime($dateString));
    }

    public static function getStatusColor($status) {
        $colors = [
            'Pending' => '#f5ebc9',
            'In Progress' => '#d7e5ff',
            'Resolved' => '#d3f3d3',
            'Rejected' => '#ffe5e5'
        ];
        return $colors[$status] ?? '#f3f4f6';
    }

    public static function getStatusTextColor($status) {
        $colors = [
            'Pending' => '#a28733',
            'In Progress' => '#3054b3',
            'Resolved' => '#2d7a2d',
            'Rejected' => '#b33c3c'
        ];
        return $colors[$status] ?? '#6b7280';
    }
}
?>
