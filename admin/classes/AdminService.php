<?php
require_once __DIR__ . '/../../db.php';

class AdminService {
    private $db;
    private $gsuite_domain = '@g.batstate-u.edu.ph';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }


    public function create($data) {
        $validation = $this->validateCreateInput($data);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        if (!$this->isGSuiteEmail($data['email'])) {
            return ['success' => false, 'message' => 'SSC Admins must use BSU G-Suite account (@g.batstate-u.edu.ph)'];
        }

        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        $result = $this->createAdmin(
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['password'],
            'ssc_admin' 
        );

        return $this->normalizeResponse($result);
    }

    public function update($data) {
        $validation = $this->validateUpdateInput($data);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        if (!$this->adminExists($data['admin_id'])) {
            return ['success' => false, 'message' => 'Admin not found'];
        }

        if ($this->isSuperAdmin($data['admin_id'])) {
            return ['success' => false, 'message' => 'Cannot modify Super Admin account'];
        }

        if (!$this->isGSuiteEmail($data['email'])) {
            return ['success' => false, 'message' => 'SSC Admins must use BSU G-Suite account (@g.batstate-u.edu.ph)'];
        }

        $currentAdmin = $this->getAdminById($data['admin_id']);
        if (!$currentAdmin) {
            return ['success' => false, 'message' => 'Admin not found'];
        }

        $newIsActive = isset($data['is_active']) ? (int)$data['is_active'] : (int)$currentAdmin['is_active'];

        $hasChanges = (
            $currentAdmin['name'] !== $data['name'] ||
            $currentAdmin['email'] !== $data['email'] ||
            $currentAdmin['phone_number'] !== $data['phone'] ||
            (int)$currentAdmin['is_active'] !== $newIsActive
        );

        if (!$hasChanges) {
            return ['success' => false, 'message' => 'No changes were made'];
        }

        if ($this->emailExistsForOther($data['email'], $data['admin_id'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        $result = $this->updateAdmin(
            $data['admin_id'],
            $data['name'],
            $data['email'],
            $data['phone'],
            'ssc_admin', 
            $newIsActive
        );

        return $this->normalizeResponse($result);
    }

    public function delete($data) {
        if (empty($data['admin_id']) || !is_numeric($data['admin_id'])) {
            return ['success' => false, 'message' => 'Invalid admin ID'];
        }

        if (!$this->adminExists($data['admin_id'])) {
            return ['success' => false, 'message' => 'Admin not found'];
        }

        if ($this->isSuperAdmin($data['admin_id'])) {
            return ['success' => false, 'message' => 'Cannot delete Super Admin account'];
        }

        if (isset($_SESSION['admin_id']) && $data['admin_id'] == $_SESSION['admin_id']) {
            return ['success' => false, 'message' => 'Cannot delete your own account'];
        }

        if ($this->adminHasResponses($data['admin_id'])) {
            $result = $this->toggleStatus([
                'admin_id' => $data['admin_id'],
                'is_active' => 0
            ]);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Admin has responses in the system. Account has been deactivated instead of deleted.'
                ];
            }
            return $result;
        }

        $result = $this->deleteAdmin($data['admin_id']);
        return $this->normalizeResponse($result);
    }

    public function toggleStatus($data) {
        if (empty($data['admin_id']) || !is_numeric($data['admin_id'])) {
            return ['success' => false, 'message' => 'Invalid admin ID'];
        }

        if (!$this->adminExists($data['admin_id'])) {
            return ['success' => false, 'message' => 'Admin not found'];
        }

        if ($this->isSuperAdmin($data['admin_id'])) {
            return ['success' => false, 'message' => 'Cannot change Super Admin account status'];
        }

        $stmt = $this->db->prepare("UPDATE admin SET is_active = ? WHERE admin_id = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("ii", $data['is_active'], $data['admin_id']);
        
        if ($stmt->execute()) {
            $stmt->close();
            $status = $data['is_active'] ? 'activated' : 'deactivated';
            return ['success' => true, 'message' => 'Admin ' . $status . ' successfully'];
        }

        $stmt->close();
        return ['success' => false, 'message' => 'Failed to update admin status'];
    }

    public function createAdmin($name, $email, $phone_number, $password, $role = 'ssc_admin') {
        if (empty($name) || empty($email) || empty($phone_number) || empty($password)) {
            return ['status' => false, 'message' => 'All fields are required'];
        }

        $emailValidation = $this->validateEmail($email);
        if (!$emailValidation['valid']) {
            return ['status' => false, 'message' => $emailValidation['message']];
        }

        if (!$this->isGSuiteEmail($email)) {
            return ['status' => false, 'message' => 'SSC Admins must use BSU G-Suite account (@g.batstate-u.edu.ph)'];
        }

        if (!preg_match('/^(09|\+639)\d{9}$/', str_replace(' ', '', $phone_number))) {
            return ['status' => false, 'message' => 'Invalid phone number format'];
        }

        if (strlen($password) < 5) {
            return ['status' => false, 'message' => 'Password must be at least 5 characters'];
        }

        $role = 'ssc_admin';

        if ($this->emailExists($email)) {
            return ['status' => false, 'message' => 'Email already exists'];
        }

        $password_hash = hash('sha256', $password);

        $stmt = $this->db->prepare("
            INSERT INTO admin (name, email, phone_number, password, role, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");

        if (!$stmt) {
            return ['status' => false, 'message' => 'Database error: ' . $this->db->error];
        }

        $stmt->bind_param("sssss", $name, $email, $phone_number, $password_hash, $role);

        if ($stmt->execute()) {
            $insert_id = $this->db->insert_id;
            $stmt->close();
            error_log("[AdminService] Created SSC Admin - ID: $insert_id, Email: $email");
            return [
                'status' => true,
                'message' => 'SSC Admin created successfully',
                'admin_id' => $insert_id
            ];
        }

        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Failed to create admin: ' . $error];
    }

    public function getAllAdmins() {
        $query = "SELECT admin_id, name, email, phone_number, role, is_active, created_at 
                  FROM admin 
                  WHERE role != 'super_admin'
                  ORDER BY created_at DESC";
        
        $result = mysqli_query($this->db, $query);
        
        if (!$result) {
            error_log("[AdminService] Query failed: " . mysqli_error($this->db));
            return [];
        }
        
        $admins = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $admins[] = $row;
        }
        
        mysqli_free_result($result);
        return $admins;
    }

    public function getAdminById($admin_id) {
        $stmt = $this->db->prepare("
            SELECT admin_id, name, email, phone_number, role, is_active
            FROM admin
            WHERE admin_id = ? LIMIT 1
        ");

        if (!$stmt) {
            return null;
        }

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

    public function updateAdmin($admin_id, $name, $email, $phone_number, $role, $is_active = 1) {
        if (empty($name) || empty($email) || empty($phone_number)) {
            return ['status' => false, 'message' => 'All fields are required'];
        }

        if ($this->isSuperAdmin($admin_id)) {
            return ['status' => false, 'message' => 'Cannot update Super Admin account'];
        }

        $emailValidation = $this->validateEmail($email);
        if (!$emailValidation['valid']) {
            return ['status' => false, 'message' => $emailValidation['message']];
        }

        if (!$this->isGSuiteEmail($email)) {
            return ['status' => false, 'message' => 'SSC Admins must use BSU G-Suite account (@g.batstate-u.edu.ph)'];
        }

        if (!preg_match('/^(09|\+639)\d{9}$/', str_replace(' ', '', $phone_number))) {
            return ['status' => false, 'message' => 'Invalid phone number format'];
        }

        $role = 'ssc_admin';

        if ($this->emailExistsForOther($email, $admin_id)) {
            return ['status' => false, 'message' => 'Email already used by another admin'];
        }

        $stmt = $this->db->prepare("
            UPDATE admin
            SET name = ?, email = ?, phone_number = ?, role = ?, is_active = ?
            WHERE admin_id = ?
        ");

        if (!$stmt) {
            return ['status' => false, 'message' => 'Database error: ' . $this->db->error];
        }

        $stmt->bind_param("sssiii", $name, $email, $phone_number, $role, $is_active, $admin_id);

        if ($stmt->execute()) {
            $stmt->close();
            error_log("[AdminService] Updated SSC Admin - ID: $admin_id");
            return ['status' => true, 'message' => 'Admin updated successfully'];
        }

        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Failed to update admin: ' . $error];
    }

    public function deleteAdmin($admin_id) {
        if ($this->isSuperAdmin($admin_id)) {
            return ['status' => false, 'message' => 'Cannot delete Super Admin'];
        }

        $stmt = $this->db->prepare("DELETE FROM admin WHERE admin_id = ?");
        if (!$stmt) {
            return ['status' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $admin_id);

        if ($stmt->execute()) {
            $stmt->close();
            return ['status' => true, 'message' => 'Admin deleted successfully'];
        }

        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Failed to delete admin: ' . $error];
    }

    private function adminHasResponses($admin_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM response WHERE admin_id = ?");
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

    private function isSuperAdmin($admin_id) {
        $stmt = $this->db->prepare("SELECT role FROM admin WHERE admin_id = ? LIMIT 1");
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $stmt->close();
            return $admin['role'] === 'super_admin';
        }
        
        $stmt->close();
        return false;
    }

    private function isGSuiteEmail($email) {
        $email = strtolower(trim($email));
        return substr($email, -strlen($this->gsuite_domain)) === $this->gsuite_domain;
    }

    private function validateEmail($email) {
        $email = trim(strtolower($email));
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }
        
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }
        
        $domain = $parts[1];
        
        if (strpos($domain, '.') === false) {
            return ['valid' => false, 'message' => 'Email must have a valid domain'];
        }
        
        $domainParts = explode('.', $domain);
        $tld = end($domainParts);
        
        if (strlen($tld) < 2) {
            return ['valid' => false, 'message' => 'Invalid email domain'];
        }
        
        if (strpos($email, '..') !== false) {
            return ['valid' => false, 'message' => 'Email cannot contain consecutive dots'];
        }
        
        return ['valid' => true];
    }

    private function validateCreateInput($data) {
        if (empty($data['name']) || strlen(trim($data['name'])) < 2) {
            return ['valid' => false, 'message' => 'Name must be at least 2 characters'];
        }

        $emailValidation = $this->validateEmail($data['email']);
        if (!$emailValidation['valid']) {
            return $emailValidation;
        }

        if (!$this->isGSuiteEmail($data['email'])) {
            return ['valid' => false, 'message' => 'SSC Admins must use BSU G-Suite account (@g.batstate-u.edu.ph)'];
        }

        if (empty($data['phone']) || !preg_match('/^(09|\+639)\d{9}$/', str_replace(' ', '', $data['phone']))) {
            return ['valid' => false, 'message' => 'Invalid phone format (use 09XXXXXXXXX or +639XXXXXXXXX)'];
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

        if (empty($data['name']) || strlen(trim($data['name'])) < 2) {
            return ['valid' => false, 'message' => 'Name must be at least 2 characters'];
        }

        $emailValidation = $this->validateEmail($data['email']);
        if (!$emailValidation['valid']) {
            return $emailValidation;
        }

        if (!$this->isGSuiteEmail($data['email'])) {
            return ['valid' => false, 'message' => 'SSC Admins must use BSU G-Suite account (@g.batstate-u.edu.ph)'];
        }

        if (empty($data['phone']) || !preg_match('/^(09|\+639)\d{9}$/', str_replace(' ', '', $data['phone']))) {
            return ['valid' => false, 'message' => 'Invalid phone format (use 09XXXXXXXXX or +639XXXXXXXXX)'];
        }

        return ['valid' => true];
    }

    private function normalizeResponse($response) {
        if (isset($response['status'])) {
            $response['success'] = $response['status'];
            unset($response['status']);
        }
        return $response;
    }

    private function emailExists($email) {
        $stmt = $this->db->prepare("SELECT admin_id FROM admin WHERE LOWER(email) = LOWER(?)");
        if (!$stmt) return false;
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    private function emailExistsForOther($email, $admin_id) {
        $stmt = $this->db->prepare("SELECT admin_id FROM admin WHERE LOWER(email) = LOWER(?) AND admin_id != ?");
        if (!$stmt) return false;
        
        $stmt->bind_param("si", $email, $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    private function adminExists($admin_id) {
        $stmt = $this->db->prepare("SELECT admin_id FROM admin WHERE admin_id = ?");
        if (!$stmt) return false;
        
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }
}
?>