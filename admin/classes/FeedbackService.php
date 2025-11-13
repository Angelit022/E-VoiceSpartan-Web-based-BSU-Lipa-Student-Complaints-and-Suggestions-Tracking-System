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
            SELECT f.feedback_id, s.first_name, s.last_name, f.rating, f.comments, f.date_given, 
                   CASE WHEN f.complaint_id IS NOT NULL THEN 'Complaint' ELSE 'Suggestion' END as type
            FROM feedback f
            JOIN student s ON f.student_id = s.student_id
            ORDER BY f.date_given DESC
        ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>