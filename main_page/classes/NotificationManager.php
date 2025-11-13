<?php
/**
 * NotificationManager - Enhanced with pagination support
 */
require_once __DIR__ . '/../../db.php';

class NotificationManager {
    private $db;
    private $studentId;

    public function __construct($studentId) {
        $this->db = (new Database())->getConnection();
        $this->studentId = $this->cleanStudentId($studentId);
    }

    private function cleanStudentId($studentId) {
        $cleaned = trim($studentId);
        if (strlen($cleaned) === 64 && ctype_xdigit($cleaned)) {
            return null;
        }
        return $cleaned;
    }

    private function studentExists() {
        if (empty($this->studentId)) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT student_id FROM student WHERE student_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $this->studentId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return !empty($result);
    }

    public function createNotification($message, $type = 'update', $complaintId = null, $suggestionId = null) {
        if (!$this->studentExists()) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO notifications (student_id, message, type, complaint_id, suggestion_id, created_at, is_read) 
             VALUES (?, ?, ?, ?, ?, NOW(), 0)"
        );
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("sssii", $this->studentId, $message, $type, $complaintId, $suggestionId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /** Get notifications with pagination support */
    public function getNotifications($limit = 10, $offset = 0) {
        if (!$this->studentExists()) {
            return [];
        }

        $stmt = $this->db->prepare(
            "SELECT notification_id, message, type, complaint_id, suggestion_id, created_at, is_read 
             FROM notifications 
             WHERE student_id = ? 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?"
        );
        
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("sii", $this->studentId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $notifications;
    }

    /** Get total count of notifications for pagination */
    public function getTotalCount() {
        if (!$this->studentExists()) {
            return 0;
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM notifications WHERE student_id = ?"
        );
        
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("s", $this->studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['count'] ?? 0;
    }

    public function getUnreadCount() {
        if (!$this->studentExists()) {
            return 0;
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM notifications WHERE student_id = ? AND is_read = 0"
        );
        
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("s", $this->studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['count'] ?? 0;
    }

    public function markAsRead($notificationId) {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND student_id = ?"
        );
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("is", $notificationId, $this->studentId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function markAllAsRead() {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET is_read = 1 WHERE student_id = ? AND is_read = 0"
        );
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $this->studentId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function getPreferences() {
        if (!$this->studentExists()) {
            return ['via_email' => 1, 'via_sms' => 0];
        }

        $stmt = $this->db->prepare(
            "SELECT via_email, via_sms FROM notification_preferences WHERE student_id = ?"
        );
        
        if (!$stmt) {
            return ['via_email' => 1, 'via_sms' => 0];
        }

        $stmt->bind_param("s", $this->studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $prefs = $result->fetch_assoc();
        $stmt->close();

        if (!$prefs) {
            $this->createDefaultPreferences();
            return ['via_email' => 1, 'via_sms' => 1];
        }

        return $prefs;
    }

    private function createDefaultPreferences() {
        $stmt = $this->db->prepare(
            "INSERT INTO notification_preferences (student_id, via_email, via_sms, created_at, updated_at) 
             VALUES (?, 1, 1, NOW(), NOW())"
        );
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $this->studentId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function updatePreferences($viaEmail, $viaSms) {
        if (!$this->studentExists()) {
            return false;
        }

        $viaEmailInt = $viaEmail ? 1 : 0;
        $viaSmsInt = $viaSms ? 1 : 0;

        $checkStmt = $this->db->prepare(
            "SELECT preference_id FROM notification_preferences WHERE student_id = ?"
        );
        
        if (!$checkStmt) {
            return false;
        }

        $checkStmt->bind_param("s", $this->studentId);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();
        
        if ($exists) {
            $stmt = $this->db->prepare(
                "UPDATE notification_preferences 
                 SET via_email = ?, via_sms = ?, updated_at = NOW() 
                 WHERE student_id = ?"
            );
            
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("iis", $viaEmailInt, $viaSmsInt, $this->studentId);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO notification_preferences (student_id, via_email, via_sms, created_at, updated_at) 
                 VALUES (?, ?, ?, NOW(), NOW())"
            );
            
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("sii", $this->studentId, $viaEmailInt, $viaSmsInt);
        }

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function deleteNotification($notificationId) {
        $stmt = $this->db->prepare(
            "DELETE FROM notifications WHERE notification_id = ? AND student_id = ?"
        );
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("is", $notificationId, $this->studentId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}
?>
