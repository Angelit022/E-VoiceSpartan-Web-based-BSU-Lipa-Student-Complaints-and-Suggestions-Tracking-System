<?php
require_once __DIR__ . '/../../db.php';

class DashboardService {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getStatistics() {
        $stats = [];

        $result = $this->db->query("SELECT COUNT(*) as count FROM complaint");
        $stats['total_complaints'] = $result->fetch_assoc()['count'];

        $result = $this->db->query("SELECT COUNT(*) as count FROM suggestion");
        $stats['total_suggestions'] = $result->fetch_assoc()['count'];

        $result = $this->db->query("SELECT COUNT(*) as count FROM complaint WHERE status_id = 1");
        $stats['pending_complaints'] = $result->fetch_assoc()['count'];

        $result = $this->db->query("SELECT COUNT(*) as count FROM complaint WHERE status_id = 2");
        $stats['in_progress_complaints'] = $result->fetch_assoc()['count'];

        $result = $this->db->query("SELECT COUNT(*) as count FROM complaint WHERE status_id = 3");
        $stats['resolved_complaints'] = $result->fetch_assoc()['count'];

        $result = $this->db->query("SELECT COUNT(*) as count FROM complaint WHERE status_id = 4");
        $stats['rejected_complaints'] = $result->fetch_assoc()['count'];

        return $stats;
    }


    public function getRecentSubmissions($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT 
                c.complaint_id AS id, 
                s.first_name, 
                s.last_name, 
                c.title, 
                st.status_name, 
                'Complaint' AS type, 
                c.date_submitted,
                c.is_anonymous
            FROM complaint c 
            JOIN student s ON c.student_id = s.student_id 
            JOIN status st ON c.status_id = st.status_id
            
            UNION ALL
            
            SELECT 
                su.suggestion_id, 
                s.first_name, 
                s.last_name, 
                su.title, 
                st.status_name, 
                'Suggestion', 
                su.date_submitted,
                su.is_anonymous
            FROM suggestion su 
            JOIN student s ON su.student_id = s.student_id 
            JOIN status st ON su.status_id = st.status_id
            
            ORDER BY date_submitted DESC
            LIMIT ?
        ");

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $submissions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $submissions;
    }


    public static function getStatusBadgeClass($status) {
        switch($status) {
            case 'Pending': return 'warning';
            case 'In Progress': return 'info';
            case 'Resolved': return 'success';
            case 'Rejected': return 'danger';
            default: return 'secondary';
        }
    }
}
?>