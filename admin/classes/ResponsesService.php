<?php

require_once __DIR__ . '/../../db.php';

class ResponsesService {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllSubmissions() {
        $result = $this->db->query("
            SELECT 
                c.complaint_id AS id, 
                s.first_name, 
                s.last_name, 
                s.email, 
                s.phone_number, 
                s.student_id, 
                c.title, 
                c.description,
                c.category,
                st.status_name,
                c.status_id,
                'Complaint' AS type, 
                c.priority, 
                c.date_submitted,
                c.is_anonymous,
                GROUP_CONCAT(
                    CONCAT(a.attachment_id, '|', a.file_path, '|', a.file_type) 
                    SEPARATOR ';;'
                ) as attachments
            FROM complaint c 
            JOIN student s ON c.student_id = s.student_id 
            JOIN status st ON c.status_id = st.status_id
            LEFT JOIN attachment a ON c.complaint_id = a.complaint_id
            GROUP BY c.complaint_id
            
            UNION ALL
            
            SELECT 
                su.suggestion_id, 
                s.first_name, 
                s.last_name, 
                s.email, 
                s.phone_number, 
                s.student_id, 
                su.title, 
                su.description,
                su.category,
                st.status_name,
                su.status_id,
                'Suggestion', 
                NULL, 
                su.date_submitted,
                su.is_anonymous,
                GROUP_CONCAT(
                    CONCAT(a.attachment_id, '|', a.file_path, '|', a.file_type) 
                    SEPARATOR ';;'
                ) as attachments
            FROM suggestion su 
            JOIN student s ON su.student_id = s.student_id 
            JOIN status st ON su.status_id = st.status_id
            LEFT JOIN attachment a ON su.suggestion_id = a.suggestion_id
            GROUP BY su.suggestion_id
            
            ORDER BY date_submitted DESC
        ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Parse attachments string into array
     * @param string $attachmentsStr Concatenated attachments
     * @return array Array of attachment objects
     */
    public static function parseAttachments($attachmentsStr) {
        if (empty($attachmentsStr)) {
            return [];
        }
        
        $attachments = [];
        $parts = explode(';;', $attachmentsStr);
        
        foreach ($parts as $part) {
            $fields = explode('|', $part);
            if (count($fields) === 3) {
                $attachments[] = [
                    'id' => $fields[0],
                    'path' => $fields[1],
                    'type' => $fields[2],
                    'filename' => basename($fields[1])
                ];
            }
        }
        
        return $attachments;
    }

    public static function getPriorityClass($priority) {
        switch($priority) {
            case 'High': return 'danger';
            case 'Medium': return 'warning';
            case 'Low': return 'info';
            default: return 'secondary';
        }
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
    

    public static function getFileIcon($fileType) {
        if (strpos($fileType, 'image') !== false) {
            return 'bi-file-image';
        } elseif (strpos($fileType, 'pdf') !== false) {
            return 'bi-file-pdf';
        } elseif (strpos($fileType, 'word') !== false || strpos($fileType, 'document') !== false) {
            return 'bi-file-word';
        } elseif (strpos($fileType, 'excel') !== false || strpos($fileType, 'spreadsheet') !== false) {
            return 'bi-file-excel';
        } else {
            return 'bi-file-earmark';
        }
    }
}
?>