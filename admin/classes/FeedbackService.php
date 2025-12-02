<?php

require_once __DIR__ . '/../../db.php';

class FeedbackService {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllFeedback() {
        $result = $this->db->query("
            SELECT 
                f.feedback_id, 
                f.rating, 
                f.date_given,
                s.student_id,
                s.first_name, 
                s.last_name,
                CASE 
                    WHEN f.complaint_id IS NOT NULL THEN 'Complaint' 
                    ELSE 'Suggestion' 
                END as type,
                CASE 
                    WHEN f.complaint_id IS NOT NULL THEN c.title
                    ELSE sg.title 
                END as title,
                CASE 
                    WHEN f.complaint_id IS NOT NULL THEN f.complaint_id
                    ELSE f.suggestion_id 
                END as submission_id
            FROM feedback f
            JOIN student s ON f.student_id = s.student_id
            LEFT JOIN complaint c ON f.complaint_id = c.complaint_id
            LEFT JOIN suggestion sg ON f.suggestion_id = sg.suggestion_id
            ORDER BY f.date_given DESC
        ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getFeedbackStats() {
        $result = $this->db->query("
            SELECT 
                COUNT(*) as total_feedback,
                AVG(rating) as avg_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
            FROM feedback
        ");

        return $result->fetch_assoc();
    }

    public function hasFeedback($submission_id, $type, $student_id) {
        $idColumn = $type === 'complaint' ? 'complaint_id' : 'suggestion_id';
        
        $stmt = $this->db->prepare("
            SELECT feedback_id 
            FROM feedback 
            WHERE $idColumn = ? AND student_id = ?
        ");
        
        $stmt->bind_param("is", $submission_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }
}
?>