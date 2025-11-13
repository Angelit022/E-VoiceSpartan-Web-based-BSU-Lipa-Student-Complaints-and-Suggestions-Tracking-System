<?php
require_once __DIR__ . '/../../db.php';

class AdminCrudHandler {
    private $db;
    private $adminService;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->adminService = new AdminService();
    }

    public function create($data) {

        $validation = $this->validateCreateInput($data);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        // Check email exists
        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Create admin
        $result = $this->adminService->createAdmin(
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['password'],
            $data['role']
        );

        // Convert 'status' to 'success' for consistency
        return $this->normalizeResponse($result);
    }


    public function update($data) {
        // Validate input
        $validation = $this->validateUpdateInput($data);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        // Check admin exists
        if (!$this->adminExists($data['admin_id'])) {
            return ['success' => false, 'message' => 'Admin not found'];
        }

        // Get current admin data to check for changes
        $currentAdmin = $this->getAdminById($data['admin_id']);
        if (!$currentAdmin) {
            return ['success' => false, 'message' => 'Admin not found'];
        }

        // Check if any data has changed
        $hasChanges = (
            $currentAdmin['name'] !== $data['name'] ||
            $currentAdmin['email'] !== $data['email'] ||
            $currentAdmin['phone_number'] !== $data['phone'] ||
            $currentAdmin['role'] !== $data['role']
        );

        if (!$hasChanges) {
            return ['success' => false, 'message' => 'No changes were made'];
        }

        // Check email uniqueness (exclude current admin)
        if ($this->emailExistsForOther($data['email'], $data['admin_id'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Update admin
        $result = $this->adminService->updateAdmin(
            $data['admin_id'],
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['role']
        );

        // Convert 'status' to 'success' for consistency
        return $this->normalizeResponse($result);
    }

    public function delete($data) {
        // Validate admin ID
        if (empty($data['admin_id']) || !is_numeric($data['admin_id'])) {
            return ['success' => false, 'message' => 'Invalid admin ID'];
        }

        // Check admin exists
        if (!$this->adminExists($data['admin_id'])) {
            return ['success' => false, 'message' => 'Admin not found'];
        }

        // Don't allow deleting self
        if ($data['admin_id'] == $_SESSION['admin_id']) {
            return ['success' => false, 'message' => 'Cannot delete your own account'];
        }

        // Delete admin
        $result = $this->adminService->deleteAdmin($data['admin_id']);
        
        // Convert 'status' to 'success' for consistency
        return $this->normalizeResponse($result);
    }

    public function toggleStatus($data) {
        // Validate
        if (empty($data['admin_id']) || !is_numeric($data['admin_id'])) {
            return ['success' => false, 'message' => 'Invalid admin ID'];
        }

        if (!$this->adminExists($data['admin_id'])) {
            return ['success' => false, 'message' => 'Admin not found'];
        }

        // Update status
        $stmt = $this->db->prepare("UPDATE admin SET is_active = ? WHERE admin_id = ?");
        $stmt->bind_param("ii", $data['is_active'], $data['admin_id']);
        
        if ($stmt->execute()) {
            $status = $data['is_active'] ? 'activated' : 'deactivated';
            return ['success' => true, 'message' => 'Admin ' . $status . ' successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update admin status'];
    }

    private function normalizeResponse($response) {
        if (isset($response['status'])) {
            $response['success'] = $response['status'];
            unset($response['status']);
        }
        return $response;
    }

    private function validateCreateInput($data) {
        if (empty($data['name']) || strlen($data['name']) < 2) {
            return ['valid' => false, 'message' => 'Name must be at least 2 characters'];
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }

        if (empty($data['phone']) || !preg_match('/^(09|\+639)\d{9}$/', $data['phone'])) {
            return ['valid' => false, 'message' => 'Invalid phone format'];
        }

        if (empty($data['password']) || strlen($data['password']) < 6) {
            return ['valid' => false, 'message' => 'Password must be at least 6 characters'];
        }

        return ['valid' => true];
    }

    private function validateUpdateInput($data) {
        if (empty($data['admin_id']) || !is_numeric($data['admin_id'])) {
            return ['valid' => false, 'message' => 'Invalid admin ID'];
        }

        if (empty($data['name']) || strlen($data['name']) < 2) {
            return ['valid' => false, 'message' => 'Name must be at least 2 characters'];
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }

        if (empty($data['phone']) || !preg_match('/^(09|\+639)\d{9}$/', $data['phone'])) {
            return ['valid' => false, 'message' => 'Invalid phone format'];
        }

        return ['valid' => true];
    }

    private function getAdminById($admin_id) {
        $stmt = $this->db->prepare("SELECT admin_id, name, email, phone_number, role FROM admin WHERE admin_id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();
        return $admin;
    }

    private function emailExists($email) {
        $stmt = $this->db->prepare("SELECT admin_id FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    private function emailExistsForOther($email, $admin_id) {
        $stmt = $this->db->prepare("SELECT admin_id FROM admin WHERE email = ? AND admin_id != ?");
        $stmt->bind_param("si", $email, $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    private function adminExists($admin_id) {
        $stmt = $this->db->prepare("SELECT admin_id FROM admin WHERE admin_id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }
}
?>