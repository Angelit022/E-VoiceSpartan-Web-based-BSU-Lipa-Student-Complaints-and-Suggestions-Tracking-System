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

        if (empty($email) || empty($password)) {
            return [
                'status' => false,
                'message' => 'Email and password are required',
                'is_admin' => false
            ];
        }

        // Check Super Admin first
        $superAdminData = $this->superAdmin->verifyCredentials($email, $password);
        if ($superAdminData !== false) {
            return [
                'status' => true,
                'message' => 'Super Admin verified',
                'is_admin' => true,
                'data' => $superAdminData,
                'admin_type' => 'super_admin'
            ];
        }

        // Check database admins (SSC_ADMIN only)
        $stmt = $this->db->prepare("
            SELECT admin_id, name, email, phone_number, role, password
            FROM admin
            WHERE LOWER(email) = LOWER(?) AND role = 'ssc_admin'
            LIMIT 1
        ");

        if (!$stmt) {
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
            return [
                'status' => false,
                'message' => 'Invalid admin credentials',
                'is_admin' => false
            ];
        }

        $admin = $result->fetch_assoc();
        $dbPassword = $admin['password'];
        $stmt->close();

        $passwordValid = false;

        // Check if bcrypt hashed
        if (substr($dbPassword, 0, 4) === '$2y$' || substr($dbPassword, 0, 4) === '$2a$') {
            $passwordValid = password_verify($password, $dbPassword);
        }
        // Check if plain text
        else if ($password === $dbPassword) {
            $passwordValid = true;
        }
        // Check if SHA256 hashed
        else if (hash('sha256', $password) === $dbPassword) {
            $passwordValid = true;
        }

        if (!$passwordValid) {
            return [
                'status' => false,
                'message' => 'Invalid admin credentials',
                'is_admin' => false
            ];
        }

        unset($admin['password']);

        return [
            'status' => true,
            'message' => 'Admin verified',
            'is_admin' => true,
            'data' => $admin,
            'admin_type' => 'database_admin'
        ];
    }

    
     //Check if email is admin email (for routing)
    public function isAdminEmail($email) {
        $email = trim($email);

        // Check Super Admin
        if ($this->superAdmin->isAdminEmail($email)) {
            return true;
        }

        // Check database (SSC Admin only)
        $stmt = $this->db->prepare("
            SELECT 1 FROM admin WHERE LOWER(email) = LOWER(?) AND role = 'ssc_admin' LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }

     // Get admin contact info (phone/email) for OTP
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

        // Check database (SSC Admin only)
        $stmt = $this->db->prepare("
            SELECT phone_number, email, name FROM admin 
            WHERE LOWER(email) = LOWER(?) AND role = 'ssc_admin' 
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $stmt->close();
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