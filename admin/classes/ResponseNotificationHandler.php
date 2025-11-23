<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../notif/SMSHandler.php';
require_once __DIR__ . '/../notif/EmailHandler.php';

class ResponseNotificationHandler {
    private $db;
    private $smsHandler;
    private $emailHandler;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->smsHandler = new SMSHandler();
        $this->emailHandler = new EmailHandler();
    }

    // Send notification to student about admin response
    public function notifyStudentOfResponse($studentId, $complaintId, $suggestionId, $message, $adminName = 'Admin') {
        try {
            // Get student info
            $stmt = $this->db->prepare("SELECT email, phone_number FROM student WHERE student_id = ?");
            if (!$stmt) return false;
            $stmt->bind_param("s", $studentId);
            $stmt->execute();
            $student = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$student) return false;

            // Get student notification preferences
            $prefStmt = $this->db->prepare(
                "SELECT via_email, via_sms FROM notification_preferences WHERE student_id = ?"
            );
            if ($prefStmt) {
                $prefStmt->bind_param("s", $studentId);
                $prefStmt->execute();
                $prefs = $prefStmt->get_result()->fetch_assoc();
                $prefStmt->close();
            }
            if (empty($prefs)) $prefs = ['via_email' => 1, 'via_sms' => 0];

            $success = false;
            $emailSent = false;
            $smsSent = false;

            // Send Email if preferred
            if ($prefs['via_email'] == 1 && !empty($student['email'])) {
                $emailSubject = "Response to Your " . ($complaintId ? "Complaint" : "Suggestion");
                $emailBody = $this->buildEmailBody($adminName, $message, $complaintId, $suggestionId);
                $emailSent = $this->emailHandler->sendEmail($student['email'], $emailSubject, $emailBody);
                if ($emailSent) $success = true;
            }

            // Send SMS if preferred
            if ($prefs['via_sms'] == 1 && !empty($student['phone_number'])) {
                $smsMessage = $this->buildSMSMessage($adminName, $message);
                $smsResult = $this->smsHandler->sendSMS($student['phone_number'], $smsMessage);

                if (is_array($smsResult) && isset($smsResult['success'])) {
                    if ($smsResult['success']) {
                        $smsSent = true;
                        $success = true;
                    }
                }
            }

            // Log notification in database
            $notifMessage = "Admin $adminName has responded to your " . 
                ($complaintId ? "complaint" : "suggestion") . 
                " (ID: # 000" . ($complaintId ?? $suggestionId) . ")";
            
            $this->logNotification($studentId, $notifMessage, 'response', $complaintId, $suggestionId);
            return $success;

        } catch (Exception $e) {
            return false;
        }
    }

    private function buildEmailBody($adminName, $message, $complaintId, $suggestionId) {
        $type = $complaintId ? "Complaint" : "Suggestion";
        $id = $complaintId ?? $suggestionId;

        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #c41e3a; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
                .response-box { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #c41e3a; }
                .footer { text-align: center; padding: 15px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'><h2>New Response from E-VoiceSpartan</h2></div>
                <div class='content'>
                    <p>Dear Student,</p>
                    <p>We have received a response to your <strong>$type</strong> (ID: # 000$id) from our team member <strong>$adminName</strong>:</p>
                    <div class='response-box'>
                        <p><strong>Response:</strong></p>
                        <p>" . nl2br(htmlspecialchars($message)) . "</p>
                    </div>
                    <p>Please log in to your E-VoiceSpartan account to view the full details and provide feedback if needed.</p>
                    <p>Best regards,<br><strong>E-VoiceSpartan Support Team</strong></p>
                </div>
                <div class='footer'><p>This is an automated message. Please do not reply.</p></div>
            </div>
        </body>
        </html>";
    }

    private function buildSMSMessage($adminName, $message) {
        $maxLength = 100;
        $truncated = substr($message, 0, $maxLength) . (strlen($message) > $maxLength ? "..." : "");
        return "E-VoiceSpartan: $adminName responded to your report: \"$truncated\". Check your account for full details.";
    }

    private function logNotification($studentId, $message, $type, $complaintId, $suggestionId) {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (student_id, message, type, complaint_id, suggestion_id, created_at, is_read) 
             VALUES (?, ?, ?, ?, ?, NOW(), 0)"
        );
        if (!$stmt) return false;

        $stmt->bind_param("sssii", $studentId, $message, $type, $complaintId, $suggestionId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
?>
