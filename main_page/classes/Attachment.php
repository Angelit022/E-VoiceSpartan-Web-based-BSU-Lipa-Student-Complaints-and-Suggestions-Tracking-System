<?php
class Attachment {
    private $conn;
    private $uploadDir = __DIR__ . '/../../uploads/complaints/';
    private $allowedMimes = ['image/jpeg', 'image/png', 'video/mp4', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    private $maxFileSize = 10 * 1024 * 1024; // 10MB

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function validateFile($file) {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'message' => 'No file selected'];
        }

        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'message' => 'File size exceeds 10MB limit'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedMimes)) {
            return ['success' => false, 'message' => 'File type not allowed. Allowed: JPG, PNG, MP4, PDF, DOC'];
        }

        return ['success' => true, 'message' => 'OK'];
    }


    public function saveAttachment($file, $complaint_id) {
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['success']) {
            return $validation;
        }

        // Generate unique filename
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        $filename = "{$complaint_id}_{$timestamp}_{$random}_" . basename($file['name']);
        $filepath = $this->uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'message' => 'Failed to upload file'];
        }

        // Get MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filepath);
        finfo_close($finfo);

        // Store in database
        $relativePath = 'uploads/complaints/' . $filename;
        $sql = "INSERT INTO attachment (complaint_id, file_path, file_type, uploaded_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error: ' . $this->conn->error];
        }

        $stmt->bind_param("iss", $complaint_id, $relativePath, $mimeType);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'File uploaded successfully', 'filename' => $filename, 'filepath' => $relativePath];
        } else {
            $stmt->close();
            // Delete the uploaded file if database insert fails
            @unlink($filepath);
            return ['success' => false, 'message' => 'Failed to save file information'];
        }
    }


    public function getAttachmentsByComplaintId($complaint_id) {
        $sql = "SELECT attachment_id, file_path, file_type, uploaded_at FROM attachment WHERE complaint_id = ? ORDER BY uploaded_at DESC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $complaint_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $attachments = [];

        while ($row = $result->fetch_assoc()) {
            $attachments[] = $row;
        }

        $stmt->close();
        return $attachments;
    }
}
?>
