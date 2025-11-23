<?php
require_once __DIR__ . '/../../db.php';

class ActivityLogger {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    public function log($user_id, $user_type, $activity_type, $activity_description) {
        if (!$this->db) {
            error_log('[ActivityLogger] Database connection failed');
            return false;
        }

        // Validate user_type
        $valid_user_types = ['student', 'admin'];
        if (!in_array($user_type, $valid_user_types)) {
            error_log('[ActivityLogger] Invalid user_type: ' . $user_type);
            return false;
        }

        // Validate activity_type
        $valid_activity_types = ['login', 'create', 'edit', 'delete', 'view', 'logout', 'other'];
        if (!in_array($activity_type, $valid_activity_types)) {
            error_log('[ActivityLogger] Invalid activity_type: ' . $activity_type);
            return false;
        }

        $ip_address = $this->getClientIP();
        $user_agent = $this->getUserAgent();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_log 
                (user_id, user_type, activity_type, activity_description, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            if (!$stmt) {
                error_log('[ActivityLogger] Prepare failed: ' . $this->db->error);
                return false;
            }

            $stmt->bind_param('ssssss', $user_id, $user_type, $activity_type, $activity_description, $ip_address, $user_agent);
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log('[ActivityLogger] Execute failed: ' . $stmt->error);
            } else {
                error_log("[ActivityLogger] Successfully logged: $user_type - $user_id - $activity_type - $activity_description");
            }

            $stmt->close();
            return $result;

        } catch (Exception $e) {
            error_log('[ActivityLogger] Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function logLogin($user_id, $user_type, $method = 'standard') {
        $description = ucfirst($user_type) . " logged in successfully via $method";
        return $this->log($user_id, $user_type, 'login', $description);
    }

    public function logLogout($user_id, $user_type) {
        $description = ucfirst($user_type) . " logged out";
        return $this->log($user_id, $user_type, 'logout', $description);
    }

    public function logCreate($user_id, $user_type, $entity_type, $entity_id = null) {
        $description = ucfirst($user_type) . " created a new $entity_type";
        if ($entity_id) {
            $description .= " (ID: # 000$entity_id)";
        }
        return $this->log($user_id, $user_type, 'create', $description);
    }

    public function logEdit($user_id, $user_type, $entity_type, $entity_id) {
        $description = ucfirst($user_type) . " edited $entity_type (ID: # 000$entity_id)";
        return $this->log($user_id, $user_type, 'edit', $description);
    }

    public function logDelete($user_id, $user_type, $entity_type, $entity_id) {
        $description = ucfirst($user_type) . " deleted $entity_type (ID: # 000$entity_id)";
        return $this->log($user_id, $user_type, 'delete', $description);
    }

    public function logView($user_id, $user_type, $entity_type, $entity_id = null) {
        $description = ucfirst($user_type) . " viewed $entity_type";
        if ($entity_id) {
            $description .= " (ID: # 000$entity_id)";
        }
        return $this->log($user_id, $user_type, 'view', $description);
    }

    public function getRecentActivities($limit = 50, $user_id = null, $activity_type = null) {
        if (!$this->db) {
            return [];
        }

        $query = "SELECT * FROM activity_log WHERE 1=1";
        $params = [];
        $types = '';

        if ($user_id) {
            $query .= " AND user_id = ?";
            $params[] = $user_id;
            $types .= 's';
        }

        if ($activity_type) {
            $query .= " AND activity_type = ?";
            $params[] = $activity_type;
            $types .= 's';
        }

        $query .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        $types .= 'i';

        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            error_log('[ActivityLogger] Get activities prepare failed: ' . $this->db->error);
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $activities = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $activities;
    }

    private function getClientIP() {
        $ip = 'UNKNOWN';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Validate IP
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        
        return 'UNKNOWN';
    }

    private function getUserAgent() {
        return isset($_SERVER['HTTP_USER_AGENT']) 
            ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) 
            : 'UNKNOWN';
    }

    public function cleanOldLogs($days = 90) {
        if (!$this->db) {
            return false;
        }

        $stmt = $this->db->prepare("
            DELETE FROM activity_log 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");

        if (!$stmt) {
            error_log('[ActivityLogger] Clean old logs prepare failed: ' . $this->db->error);
            return false;
        }

        $stmt->bind_param('i', $days);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}
?>