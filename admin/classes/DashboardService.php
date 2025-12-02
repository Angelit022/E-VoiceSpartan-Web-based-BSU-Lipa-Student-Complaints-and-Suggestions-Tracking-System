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

    public function getAnalyticsData() {
        $analytics = [];

        $analytics['months'] = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $analytics['complaint_trend'] = array_fill(0, 12, 0);
        $analytics['suggestion_trend'] = array_fill(0, 12, 0);

        $complaint_result = $this->db->query("SELECT MONTH(date_submitted) AS month, COUNT(*) AS cnt FROM complaint GROUP BY MONTH(date_submitted)");
        while ($row = $complaint_result->fetch_assoc()) {
            $analytics['complaint_trend'][$row['month'] - 1] = (int)$row['cnt'];
        }

        $suggestion_result = $this->db->query("SELECT MONTH(date_submitted) AS month, COUNT(*) AS cnt FROM suggestion GROUP BY MONTH(date_submitted)");
        while ($row = $suggestion_result->fetch_assoc()) {
            $analytics['suggestion_trend'][$row['month'] - 1] = (int)$row['cnt'];
        }

        $all_complaint_categories = [
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

        $analytics['complaint_categories'] = array_fill_keys($all_complaint_categories, 0);
        $result = $this->db->query("SELECT category, COUNT(*) AS cnt FROM complaint WHERE category != '' GROUP BY category");
        while ($row = $result->fetch_assoc()) {
            if (isset($analytics['complaint_categories'][$row['category']])) {
                $analytics['complaint_categories'][$row['category']] = (int)$row['cnt'];
            }
        }

        $all_suggestion_categories = [
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

        $analytics['suggestion_categories'] = array_fill_keys($all_suggestion_categories, 0);
        $result = $this->db->query("SELECT category, COUNT(*) AS cnt FROM suggestion WHERE category != '' GROUP BY category");
        while ($row = $result->fetch_assoc()) {
            if (isset($analytics['suggestion_categories'][$row['category']])) {
                $analytics['suggestion_categories'][$row['category']] = (int)$row['cnt'];
            }
        }

        $analytics['complaint_statuses'] = ['Pending' => 0, 'In Progress' => 0, 'Resolved' => 0, 'Rejected' => 0];
        $result = $this->db->query("SELECT s.status_name, COUNT(*) AS cnt FROM complaint c JOIN status s ON c.status_id=s.status_id GROUP BY s.status_name");
        while ($row = $result->fetch_assoc()) {
            $analytics['complaint_statuses'][$row['status_name']] = (int)$row['cnt'];
        }

        $analytics['suggestion_statuses'] = ['Pending' => 0, 'In Progress' => 0, 'Resolved' => 0, 'Rejected' => 0];
        $result = $this->db->query("SELECT s.status_name, COUNT(*) AS cnt FROM suggestion sg JOIN status s ON sg.status_id=s.status_id GROUP BY s.status_name");
        while ($row = $result->fetch_assoc()) {
            $analytics['suggestion_statuses'][$row['status_name']] = (int)$row['cnt'];
        }

        return $analytics;
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

    public static function getStatusBadgeColor($status) {
        switch($status) {
            case 'Pending': return '#FFEB3B';
            case 'In Progress': return '#64B5F6';
            case 'Resolved': return '#81C784';
            case 'Rejected': return '#E57373';
            default: return '#6c757d';
        }
    }
}
?>