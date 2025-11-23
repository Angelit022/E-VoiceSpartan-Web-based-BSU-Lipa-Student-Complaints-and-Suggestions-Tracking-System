<?php
require_once __DIR__ . '/../../admin/classes/SuperAdminAccount.php';
require_once __DIR__ . '/../../db.php';

class AdminAuthService {
    private $db;
    private $superAdmin;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->superAdmin = new SuperAdminAccount();
    }

    public function authenticateAdmin($email, $password) {
        $email = trim($email);
        $password = trim($password);

        error_log("[AdminAuthService] Starting authentication for: $email");

        if (empty($email) || empty($password)) {
            error_log("[AdminAuthService] Empty credentials");
            return [
                'status' => false,
                'message' => 'Email and password are required',
                'is_admin' => false
            ];
        }

        // Check Super Admin first
        $superAdminData = $this->superAdmin->verifyCredentials($email, $password);
        if ($superAdminData !== false) {
            error_log("[AdminAuthService] Super Admin authenticated");
            return [
                'status' => true,
                'message' => 'Super Admin verified',
                'is_admin' => true,
                'data' => $superAdminData,
                'admin_type' => 'super_admin'
            ];
        }

        // Check database admins - FIXED: Check ALL admins except admin_id = 1
        $stmt = $this->db->prepare("
            SELECT admin_id, name, email, phone_number, role, password, is_active
            FROM admin
            WHERE LOWER(email) = LOWER(?) AND admin_id != 1
            LIMIT 1
        ");

        if (!$stmt) {
            error_log("[AdminAuthService] Database prepare failed: " . $this->db->error);
            return [
                'status' => false,
                'message' => 'Database error',
                'is_admin' => false
            ];
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            error_log("[AdminAuthService] No admin found for email: $email");
            $stmt->close();
            return [
                'status' => false,
                'message' => 'Invalid admin credentials',
                'is_admin' => false
            ];
        }

        $admin = $result->fetch_assoc();
        $stmt->close();

        error_log("[AdminAuthService] Admin found - ID: {$admin['admin_id']}, Role: '{$admin['role']}', Active: {$admin['is_active']}");

        // Check if admin is active
        if ($admin['is_active'] != 1) {
            error_log("[AdminAuthService] Admin inactive");
            return [
                'status' => false,
                'message' => 'Your account has been deactivated. Please contact Super Admin.',
                'is_admin' => false
            ];
        }

        // FIXED: Update role if empty or not set to ssc_admin
        if (empty($admin['role']) || $admin['role'] !== 'ssc_admin') {
            error_log("[AdminAuthService] Updating role from '{$admin['role']}' to 'ssc_admin'");
            $updateStmt = $this->db->prepare("UPDATE admin SET role = 'ssc_admin' WHERE admin_id = ?");
            $updateStmt->bind_param("i", $admin['admin_id']);
            $updateStmt->execute();
            $updateStmt->close();
            $admin['role'] = 'ssc_admin';
        }

        // Verify password
        $passwordValid = false;
        $dbPassword = $admin['password'];

        // Check different hash types
        if (strlen($dbPassword) === 64 && ctype_xdigit($dbPassword)) {
            // SHA-256 hash (64 hex characters)
            $passwordValid = (hash('sha256', $password) === $dbPassword);
            error_log("[AdminAuthService] Checking SHA-256 hash: " . ($passwordValid ? 'MATCH' : 'NO MATCH'));
        } elseif (substr($dbPassword, 0, 4) === '$2y$' || substr($dbPassword, 0, 4) === '$2a$') {
            // Bcrypt hash
            $passwordValid = password_verify($password, $dbPassword);
            error_log("[AdminAuthService] Checking bcrypt hash: " . ($passwordValid ? 'MATCH' : 'NO MATCH'));
        } else {
            // Plain text (should not be used in production)
            $passwordValid = ($password === $dbPassword);
            error_log("[AdminAuthService] Checking plain text: " . ($passwordValid ? 'MATCH' : 'NO MATCH'));
        }

        if (!$passwordValid) {
            error_log("[AdminAuthService] Password verification failed");
            return [
                'status' => false,
                'message' => 'Invalid admin credentials',
                'is_admin' => false
            ];
        }

        unset($admin['password']);

        error_log("[AdminAuthService] Authentication successful for: {$admin['email']}");
        return [
            'status' => true,
            'message' => 'Admin verified',
            'is_admin' => true,
            'data' => $admin,
            'admin_type' => 'database_admin'
        ];
    }

    public function isAdminEmail($email) {
        $email = trim($email);

        // Check Super Admin
        if ($this->superAdmin->isAdminEmail($email)) {
            return true;
        }

        // Check database - any admin except admin_id = 1
        $stmt = $this->db->prepare("
            SELECT 1 FROM admin WHERE LOWER(email) = LOWER(?) AND admin_id != 1 LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function getAdminContactInfo($email) {
        // Check Super Admin
        if ($this->superAdmin->isAdminEmail($email)) {
            return [
                'phone' => $this->superAdmin->getPhoneNumber(),
                'email' => $this->superAdmin->getEmail(),
                'name' => $this->superAdmin->getName(),
                'type' => 'super_admin'
            ];
        }

        // Check database - any admin except admin_id = 1
        $stmt = $this->db->prepare("
            SELECT phone_number, email, name, is_active FROM admin 
            WHERE LOWER(email) = LOWER(?) AND admin_id != 1
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $stmt->close();
            
            if ($admin['is_active'] != 1) {
                return null;
            }
            
            return [
                'phone' => $admin['phone_number'],
                'email' => $admin['email'],
                'name' => $admin['name'],
                'type' => 'database_admin'
            ];
        }

        $stmt->close();
        return null;
    }
}
?>