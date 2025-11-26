<?php
/**
 * ActivityLogService - Handles all activity log operations with sorting
 */
require_once __DIR__ . '/../../db.php';

class ActivityLogService {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Get activity logs with optional filters and sorting
     */
    public function getActivityLogs($filters = []) {
        $query = "SELECT 
                    log_id, 
                    user_id, 
                    user_type, 
                    activity_type, 
                    activity_description, 
                    ip_address, 
                    user_agent, 
                    created_at
                  FROM activity_log 
                  WHERE 1=1";
        
        $params = [];
        $types = '';

        // User filter (user_id or email)
        if (!empty($filters['user_filter'])) {
            $query .= " AND user_id LIKE ?";
            $params[] = '%' . $filters['user_filter'] . '%';
            $types .= 's';
        }

        // User type filter
        if (!empty($filters['type_filter'])) {
            $query .= " AND user_type = ?";
            $params[] = $filters['type_filter'];
            $types .= 's';
        }

        // Activity type filter
        if (!empty($filters['activity_filter'])) {
            $query .= " AND activity_type = ?";
            $params[] = $filters['activity_filter'];
            $types .= 's';
        }

        // Date from filter
        if (!empty($filters['date_from'])) {
            $query .= " AND DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
            $types .= 's';
        }

        // Date to filter
        if (!empty($filters['date_to'])) {
            $query .= " AND DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
            $types .= 's';
        }

        // Sorting
        $sort_by = isset($filters['sort_by']) ? $filters['sort_by'] : 'created_at';
        $sort_order = isset($filters['sort_order']) ? $filters['sort_order'] : 'DESC';
        
        // Whitelist allowed sort columns for security
        $allowed_sort_columns = ['created_at', 'user_id', 'user_type', 'activity_type', 'log_id'];
        if (!in_array($sort_by, $allowed_sort_columns)) {
            $sort_by = 'created_at';
        }
        
        // Whitelist sort order
        $sort_order = strtoupper($sort_order);
        if ($sort_order !== 'ASC' && $sort_order !== 'DESC') {
            $sort_order = 'DESC';
        }
        
        $query .= " ORDER BY " . $sort_by . " " . $sort_order;

        // Limit
        $limit = isset($filters['limit']) ? intval($filters['limit']) : 50;
        $query .= " LIMIT ?";
        $params[] = $limit;
        $types .= 'i';

        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $logs = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $logs;
    }

    /**
     * Get activity statistics
     */
    public function getActivityStats() {
        $stats = [
            'total' => 0,
            'students' => 0,
            'admins' => 0,
            'today' => 0
        ];

        // Total activities
        $result = $this->db->query("SELECT COUNT(*) as count FROM activity_log");
        if ($result) {
            $stats['total'] = $result->fetch_assoc()['count'];
        }

        // Student activities
        $result = $this->db->query("SELECT COUNT(*) as count FROM activity_log WHERE user_type = 'student'");
        if ($result) {
            $stats['students'] = $result->fetch_assoc()['count'];
        }

        // Admin activities
        $result = $this->db->query("SELECT COUNT(*) as count FROM activity_log WHERE user_type = 'admin'");
        if ($result) {
            $stats['admins'] = $result->fetch_assoc()['count'];
        }

        // Today's activities
        $result = $this->db->query("SELECT COUNT(*) as count FROM activity_log WHERE DATE(created_at) = CURDATE()");
        if ($result) {
            $stats['today'] = $result->fetch_assoc()['count'];
        }

        return $stats;
    }

    /**
     * Get activity logs for dashboard (limited view)
     */
    public function getRecentActivityLogs($limit = 10) {
        $query = "SELECT 
                    log_id, 
                    user_id, 
                    user_type, 
                    activity_type, 
                    activity_description, 
                    created_at
                  FROM activity_log
                  ORDER BY created_at DESC
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $logs = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $logs;
    }

    /**
     * Clean old activity logs (older than specified days)
     */
    public function cleanOldLogs($days = 90) {
        $query = "DELETE FROM activity_log 
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $days);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Export activity logs to CSV
     */
    public function exportToCSV($filters = []) {
        $logs = $this->getActivityLogs($filters);
        
        if (empty($logs)) {
            return false;
        }

        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'Log ID',
            'User ID',
            'User Type',
            'Activity Type',
            'Description',
            'IP Address',
            'User Agent',
            'Date & Time'
        ]);
        
        // CSV data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['log_id'],
                $log['user_id'],
                ucfirst($log['user_type']),
                ucfirst($log['activity_type']),
                $log['activity_description'],
                $log['ip_address'] ?? 'N/A',
                $log['user_agent'] ?? 'N/A',
                date('M d, Y h:i:s A', strtotime($log['created_at']))
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Get activity breakdown by type
     */
    public function getActivityBreakdown() {
        $query = "SELECT 
                    activity_type,
                    COUNT(*) as count
                  FROM activity_log
                  GROUP BY activity_type
                  ORDER BY count DESC";
        
        $result = $this->db->query($query);
        
        if (!$result) {
            return [];
        }

        $breakdown = [];
        while ($row = $result->fetch_assoc()) {
            $breakdown[$row['activity_type']] = (int)$row['count'];
        }

        return $breakdown;
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary($user_id, $user_type) {
        $query = "SELECT 
                    activity_type,
                    COUNT(*) as count,
                    MAX(created_at) as last_activity
                  FROM activity_log
                  WHERE user_id = ? AND user_type = ?
                  GROUP BY activity_type
                  ORDER BY count DESC";
        
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('ss', $user_id, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $summary;
    }
}
?>