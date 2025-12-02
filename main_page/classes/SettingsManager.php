<?php
require_once __DIR__ . '/../../db.php';

class SettingsManager {
    private $db;
    private $studentId;

    public function __construct($studentId) {
        $this->db = (new Database())->getConnection();
        $this->studentId = $this->cleanStudentId($studentId);
    }

    private function cleanStudentId($studentId) {
        if (strlen($studentId) === 64 && ctype_xdigit($studentId)) {
            error_log("Warning: Hashed student ID used in SettingsManager: $studentId");
        }
        return trim($studentId);
    }

    private function studentExists() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM student WHERE student_id = ?");
        if (!$stmt) {
            error_log("Failed to prepare student check query: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("s", $this->studentId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return ($result['count'] ?? 0) > 0;
    }

    public function getStudentInfo() {
        if (!$this->studentExists()) {
            error_log("Student not found: " . $this->studentId);
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT student_id, first_name, last_name, email, phone_number 
             FROM student WHERE student_id = ?"
        );
        
        if (!$stmt) {
            error_log("Failed to prepare query: " . $this->db->error);
            return null;
        }

        $stmt->bind_param("s", $this->studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();

        return $student;
    }

    public function updatePhoneNumber($phoneNumber) {
    if (!$this->studentExists()) {
        error_log("Cannot update - student not found: " . $this->studentId);
        return false;
    }

    $stmt = $this->db->prepare(
        "UPDATE student 
         SET phone_number = ? 
         WHERE student_id = ?"
    );
    
    if (!$stmt) {
        error_log("Failed to prepare update query: " . $this->db->error);
        return false;
    }

    $stmt->bind_param("ss", $phoneNumber, $this->studentId);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Failed to update phone number: " . $stmt->error);
    }
    
    $stmt->close();
    return $result;
}

    public function updateAccountInfo($firstName, $lastName, $email, $phoneNumber) {
        if (!$this->studentExists()) {
            error_log("Cannot update - student not found: " . $this->studentId);
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE student 
             SET first_name = ?, last_name = ?, email = ?, phone_number = ? 
             WHERE student_id = ?"
        );
        
        if (!$stmt) {
            error_log("Failed to prepare update query: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("sssss", $firstName, $lastName, $email, $phoneNumber, $this->studentId);
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Failed to update account: " . $stmt->error);
        }
        
        $stmt->close();
        return $result;
    }

    public function verifyPassword($currentPassword) {
        $stmt = $this->db->prepare("SELECT password FROM student WHERE student_id = ?");
        
        if (!$stmt) {
            error_log("Failed to prepare password check: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("s", $this->studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();

        if (!$student) {
            return false;
        }

        // Check if password is hashed with password_hash or with hash()
        if (password_get_info($student['password'])['algo'] !== null) {
            // It's a bcrypt hash
            return password_verify($currentPassword, $student['password']);
        } else {
            // It's a SHA-256 hash
            return hash('sha256', $currentPassword) === $student['password'];
        }
    }

    public function updatePassword($newPassword) {
        $hashedPassword = hash('sha256', $newPassword);
        
        $stmt = $this->db->prepare("UPDATE student SET password = ? WHERE student_id = ?");
        
        if (!$stmt) {
            error_log("Failed to prepare password update: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("ss", $hashedPassword, $this->studentId);
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Failed to update password: " . $stmt->error);
        }
        
        $stmt->close();
        return $result;
    }


    public function updatePreferences($language, $theme) {
        if (!$this->studentExists()) {
            error_log("Cannot update preferences - student not found: " . $this->studentId);
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO user_preferences (student_id, language, theme) 
             VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE language = VALUES(language), theme = VALUES(theme)"
        );
        
        if (!$stmt) {
            error_log("Failed to prepare preferences update: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("sss", $this->studentId, $language, $theme);
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Failed to update preferences: " . $stmt->error);
        }
        
        $stmt->close();
        return $result;
    }
}
?>