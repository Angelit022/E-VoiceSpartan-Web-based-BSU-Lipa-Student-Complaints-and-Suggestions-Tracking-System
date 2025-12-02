<?php
require_once __DIR__ . '/../../signup_login/classes/ActivityLogger.php';

class StudentActivityLog extends ActivityLogger {
    private $student_id;
    
    public function __construct($student_id) {
        parent::__construct();
        $this->student_id = $student_id;
    }

    public function logStudentLogout() {
        $description = "Student logged out";
        return $this->log($this->student_id, 'student', 'logout', $description);
    }

    public function logComplaintCreate($complaint_id, $is_anonymous = false) {
        $description = $is_anonymous 
            ? "Student created a new complaint anonymously (ID: # 000{$complaint_id})"
            : "Student created a new complaint (ID: # 000{$complaint_id})";
        
        return $this->log($this->student_id, 'student', 'create', $description);
    }

    public function logComplaintEdit($complaint_id) {
        return $this->logEdit($this->student_id, 'student', 'complaint', $complaint_id);
    }


    public function logComplaintDelete($complaint_id) {
        return $this->logDelete($this->student_id, 'student', 'complaint', $complaint_id);
    }

    public function logComplaintView($complaint_id) {
        return $this->logView($this->student_id, 'student', 'complaint', $complaint_id);
    }

    public function logSuggestionCreate($suggestion_id, $is_anonymous = false) {
        $description = $is_anonymous 
            ? "Student created a new suggestion anonymously (ID: # 000{$suggestion_id})"
            : "Student created a new suggestion (ID: # 000{$suggestion_id})";
        
        return $this->log($this->student_id, 'student', 'create', $description);
    }

    public function logSuggestionEdit($suggestion_id) {
        return $this->logEdit($this->student_id, 'student', 'suggestion', $suggestion_id);
    }

    public function logSuggestionDelete($suggestion_id) {
        return $this->logDelete($this->student_id, 'student', 'suggestion', $suggestion_id);
    }

    public function logSuggestionView($suggestion_id) {
        return $this->logView($this->student_id, 'student', 'suggestion', $suggestion_id);
    }

    public function logAttachmentUpload($complaint_id, $filename) {
        $description = "Student uploaded attachment '{$filename}' to complaint (ID: # 000{$complaint_id})";
        return $this->log($this->student_id, 'student', 'create', $description);
    }

    public function logAttachmentDelete($attachment_id, $filename) {
        $description = "Student deleted attachment '{$filename}' (Attachment ID: # 000{$attachment_id})";
        return $this->log($this->student_id, 'student', 'delete', $description);
    }

    public function logFeedbackSubmit($type, $id, $rating) {
        $entityType = ucfirst($type);
        $description = "Student submitted {$rating}-star feedback for {$entityType} (ID: # 000{$id})";
        return $this->log($this->student_id, 'student', 'create', $description);
    }

    public function logResponseView($type, $id) {
        $entityType = ucfirst($type);
        $description = "Student viewed admin response for {$entityType} (ID: # 000{$id})";
        return $this->log($this->student_id, 'student', 'view', $description);
    }

    public function logSettingsUpdate($setting_type) {
        $descriptions = [
            'account' => 'Student updated account information',
            'password' => 'Student changed password',
            'notifications' => 'Student updated notification preferences',
            'preferences' => 'Student updated user preferences'
        ];
        
        $description = $descriptions[$setting_type] ?? "Student updated {$setting_type} settings";
        return $this->log($this->student_id, 'student', 'edit', $description);
    }

    public function logNotificationMarkRead($notification_id) {
        $description = "Student marked notification as read (ID: # 000{$notification_id})";
        return $this->log($this->student_id, 'student', 'other', $description);
    }

    public function logNotificationMarkAllRead() {
        $description = "Student marked all notifications as read";
        return $this->log($this->student_id, 'student', 'other', $description);
    }

    public function logNotificationDelete($notification_id) {
        $description = "Student deleted notification (ID: # 000{$notification_id})";
        return $this->log($this->student_id, 'student', 'delete', $description);
    }

    public function logProfileView() {
        $description = "Student viewed their profile";
        return $this->log($this->student_id, 'student', 'view', $description);
    }

    public function logSubmissionView($type, $id) {
        $entityType = ucfirst($type);
        $description = "Student viewed {$entityType} details (ID: # 000{$id})";
        return $this->log($this->student_id, 'student', 'view', $description);
    }

    public function logCustomActivity($activity_type, $description) {
        $valid_types = ['login', 'create', 'edit', 'delete', 'view', 'logout', 'other'];
        if (!in_array($activity_type, $valid_types)) {
            $activity_type = 'other';
        }
        
        return $this->log($this->student_id, 'student', $activity_type, $description);
    }
}
?>