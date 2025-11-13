<?php
/**
 * AdminService - Manage SSC Admins (CRUD operations)
 * Only Super Admin can perform these operations
 * Updated: Removed Staff role completely
 */
require_once __DIR__ . '/../../db.php';

class AdminService {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }


    public function createAdmin($name, $email, $phone_number, $password, $role = 'ssc_admin') {
        if (empty($name) || empty($email) || empty($phone_number) || empty($password)) {
            return [
                'status' => false,
                'message' => 'All fields are required'
            ];
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => false,
                'message' => 'Invalid email format'
            ];
        }

        // Validate phone number
        if (!preg_match('/^(\+63|0)?9\d{9}$/', str_replace(' ', '', $phone_number))) {
            return [
                'status' => false,
                'message' => 'Invalid phone number format'
            ];
        }

        // Validate password strength
        if (strlen($password) < 5) {
            return [
                'status' => false,
                'message' => 'Password must be at least 5 characters'
            ];
        }

        // Force role to be ssc_admin only
        if ($role !== 'ssc_admin') {
            $role = 'ssc_admin';
        }

        // Check if email already exists
        $stmt = $this->db->prepare("SELECT admin_id FROM admin WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            return [
                'status' => false,
                'message' => 'Email already exists'
            ];
        }
        $stmt->close();

        $password_hash = hash('sha256', $password);

        $stmt = $this->db->prepare("
            INSERT INTO admin (name, email, phone_number, password, role)
            VALUES (?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            return [
                'status' => false,
                'message' => 'Database error: ' . $this->db->error
            ];
        }

        $stmt->bind_param("sssss", $name, $email, $phone_number, $password_hash, $role);

        if ($stmt->execute()) {
            $stmt->close();
            return [
                'status' => true,
                'message' => 'Admin created successfully',
                'admin_id' => $this->db->insert_id
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return [
                'status' => false,
                'message' => 'Failed to create admin: ' . $error
            ];
        }
    }

    /**
     * Get all SSC Admins (excluding Super Admin)
     */
    public function getAllAdmins() {
        $stmt = $this->db->prepare("
            SELECT admin_id, name, email, phone_number, role
            FROM admin
            WHERE role = 'ssc_admin'
            ORDER BY admin_id DESC
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $admins = [];

        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }

        $stmt->close();
        return $admins;
    }

    /**
     * Get single admin by ID
     */
    public function getAdminById($admin_id) {
        $stmt = $this->db->prepare("
            SELECT admin_id, name, email, phone_number, role
            FROM admin
            WHERE admin_id = ? LIMIT 1
        ");

        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $stmt->close();
            return $admin;
        }

        $stmt->close();
        return null;
    }

    /**
     * Update admin information
     */
    public function updateAdmin($admin_id, $name, $email, $phone_number, $role) {
        if (empty($name) || empty($email) || empty($phone_number)) {
            return [
                'status' => false,
                'message' => 'All fields are required'
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => false,
                'message' => 'Invalid email format'
            ];
        }

        if (!preg_match('/^(\+63|0)?9\d{9}$/', str_replace(' ', '', $phone_number))) {
            return [
                'status' => false,
                'message' => 'Invalid phone number format'
            ];
        }

        // Force role to be ssc_admin only (cannot change to super_admin)
        if ($role !== 'ssc_admin') {
            $role = 'ssc_admin';
        }

        // Check if email is already used by another admin
        $stmt = $this->db->prepare("
            SELECT admin_id FROM admin
            WHERE email = ? AND admin_id != ?
            LIMIT 1
        ");
        $stmt->bind_param("si", $email, $admin_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            return [
                'status' => false,
                'message' => 'Email already used by another admin'
            ];
        }
        $stmt->close();

        $stmt = $this->db->prepare("
            UPDATE admin
            SET name = ?, email = ?, phone_number = ?, role = ?
            WHERE admin_id = ?
        ");

        if (!$stmt) {
            return [
                'status' => false,
                'message' => 'Database error'
            ];
        }

        $stmt->bind_param("ssssi", $name, $email, $phone_number, $role, $admin_id);

        if ($stmt->execute()) {
            $stmt->close();
            return [
                'status' => true,
                'message' => 'Admin updated successfully'
            ];
        } else {
            $stmt->close();
            return [
                'status' => false,
                'message' => 'Failed to update admin'
            ];
        }
    }

    /**
     * Delete admin
     */
    public function deleteAdmin($admin_id) {
        if ($admin_id === 1) {
            return [
                'status' => false,
                'message' => 'Cannot delete Super Admin'
            ];
        }

        $stmt = $this->db->prepare("DELETE FROM admin WHERE admin_id = ?");
        $stmt->bind_param("i", $admin_id);

        if ($stmt->execute()) {
            $stmt->close();
            return [
                'status' => true,
                'message' => 'Admin deleted successfully'
            ];
        } else {
            $stmt->close();
            return [
                'status' => false,
                'message' => 'Failed to delete admin'
            ];
        }
    }

    /**
     * Reset admin password
     */
    public function resetPassword($admin_id, $new_password) {
        if (strlen($new_password) < 5) {
            return [
                'status' => false,
                'message' => 'Password must be at least 5 characters'
            ];
        }

        $password_hash = hash('sha256', $new_password);

        $stmt = $this->db->prepare("
            UPDATE admin SET password = ? WHERE admin_id = ?
        ");

        $stmt->bind_param("si", $password_hash, $admin_id);

        if ($stmt->execute()) {
            $stmt->close();
            return [
                'status' => true,
                'message' => 'Password reset successfully'
            ];
        } else {
            $stmt->close();
            return [
                'status' => false,
                'message' => 'Failed to reset password'
            ];
        }
    }
}
?>