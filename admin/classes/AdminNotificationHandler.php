<?php
require_once __DIR__ . '/../../db.php';

class AdminNotificationHandler {
    private $db;
    private $adminId;

    public function __construct($adminId = null) {
        $this->db = (new Database())->getConnection();
        $this->adminId = $adminId;
    }


    public function notifyStudentOfResponse($studentId, $complaintId, $suggestionId, $message, $type = 'response') {
        // Create notification in database
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (student_id, message, type, complaint_id, suggestion_id, created_at, is_read) 
             VALUES (?, ?, ?, ?, ?, NOW(), 0)"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("sssii", $studentId, $message, $type, $complaintId, $suggestionId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }


    public function saveResponse($complaintId, $suggestionId, $adminId, $message) {
        $stmt = $this->db->prepare(
            "INSERT INTO response (complaint_id, suggestion_id, admin_id, message, date_responded) 
             VALUES (?, ?, ?, ?, NOW())"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iis", $complaintId, $suggestionId, $adminId, $message);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }


    public function getResponse($complaintId, $suggestionId) {
        $stmt = $this->db->prepare(
            "SELECT r.response_id, r.message, r.date_responded, a.name as admin_name 
             FROM response r 
             LEFT JOIN admin a ON r.admin_id = a.admin_id 
             WHERE r.complaint_id = ? OR r.suggestion_id = ?"
        );

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("ii", $complaintId, $suggestionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $response = $result->fetch_assoc();
        $stmt->close();

        return $response;
    }
}
?>