<?php
require_once __DIR__ . '/../../signup_login/classes/ActivityLogger.php';

class AdminActivityLog extends ActivityLogger {
    private $admin_id;
    private $admin_email;
    private $admin_name;
    
    public function __construct($admin_id, $admin_name = 'Admin', $admin_email = null) {
        parent::__construct();
        $this->admin_id = $admin_id;
        $this->admin_name = $admin_name;
        // Use email for logging if available, otherwise fall back to ID
        $this->admin_email = $admin_email ?? $_SESSION['authUser'] ?? $admin_id;
    }

    /**
     * Override parent log method to use email instead of numeric ID
     */
    private function logAsAdmin($activity_type, $activity_description) {
        // Use email for user_id to properly identify admins in activity logs
        return $this->log($this->admin_email, 'admin', $activity_type, $activity_description);
    }

    /**
     * Log admin login
     */
    public function logAdminLogin($method = 'Gmail OTP') {
        $description = "Admin {$this->admin_name} logged in successfully via {$method}";
        return $this->logAsAdmin('login', $description);
    }

    /**
     * Log admin logout
     */
    public function logAdminLogout() {
        $description = "Admin {$this->admin_name} logged out";
        return $this->logAsAdmin('logout', $description);
    }

    /**
     * Log exporting activity logs
     */
    public function logActivityLogsExport($format = 'CSV') {
        $description = "Admin exported activity logs to {$format}";
        return $this->logAsAdmin('other', $description);
    }

    /**
     * Log complaint status update
     */
    public function logComplaintStatusUpdate($complaint_id, $old_status, $new_status) {
        $description = "Admin updated complaint (ID: #000{$complaint_id}) status from '{$old_status}' to '{$new_status}'";
        return $this->logAsAdmin('edit', $description);
    }

    /**
     * Log suggestion status update
     */
    public function logSuggestionStatusUpdate($suggestion_id, $old_status, $new_status) {
        $description = "Admin updated suggestion (ID: #000{$suggestion_id}) status from '{$old_status}' to '{$new_status}'";
        return $this->logAsAdmin('edit', $description);
    }

    /**
     * Log complaint response
     */
    public function logComplaintResponse($complaint_id, $student_id, $is_anonymous = false) {
        $recipient = $is_anonymous ? "anonymous student" : "student {$student_id}";
        $description = "Admin sent response to {$recipient} for complaint (ID: #000{$complaint_id})";
        return $this->logAsAdmin('create', $description);
    }

    /**
     * Log suggestion response
     */
    public function logSuggestionResponse($suggestion_id, $student_id, $is_anonymous = false) {
        $recipient = $is_anonymous ? "anonymous student" : "student {$student_id}";
        $description = "Admin sent response to {$recipient} for suggestion (ID: #000{$suggestion_id})";
        return $this->logAsAdmin('create', $description);
    }

    /**
     * Log admin account creation
     */
    public function logAdminCreate($new_admin_id, $new_admin_name, $new_admin_email) {
        $description = "Admin created new admin account: {$new_admin_name} ({$new_admin_email}) (ID: #000{$new_admin_id})";
        return $this->logAsAdmin('create', $description);
    }

    /**
     * Log admin account update
     */
    public function logAdminUpdate($target_admin_id, $target_admin_name, $changes = []) {
        $changeText = !empty($changes) ? " - Changes: " . implode(", ", $changes) : "";
        $description = "Admin updated admin account: {$target_admin_name} (ID: #000{$target_admin_id}){$changeText}";
        return $this->logAsAdmin('edit', $description);
    }

    /**
     * Log admin account deletion
     */
    public function logAdminDelete($deleted_admin_id, $deleted_admin_name) {
        $description = "Admin deleted admin account: {$deleted_admin_name} (ID: #000{$deleted_admin_id})";
        return $this->logAsAdmin('delete', $description);
    }

    /**
     * Log admin account deactivation
     */
    public function logAdminDeactivate($target_admin_id, $target_admin_name) {
        $description = "Admin deactivated admin account: {$target_admin_name} (ID: #000{$target_admin_id})";
        return $this->logAsAdmin('edit', $description);
    }

    /**
     * Log viewing pages
     */
    public function logPageView($page_name) {
        $description = "Admin viewed {$page_name}";
        return $this->logAsAdmin('view', $description);
    }

    /**
     * Generic activity log for custom admin messages
     */
    public function logCustomActivity($activity_type, $description) {
        $valid_types = ['login', 'create', 'edit', 'delete', 'view', 'logout', 'other'];
        if (!in_array($activity_type, $valid_types)) {
            $activity_type = 'other';
        }
        
        return $this->logAsAdmin($activity_type, $description);
    }
}
?>