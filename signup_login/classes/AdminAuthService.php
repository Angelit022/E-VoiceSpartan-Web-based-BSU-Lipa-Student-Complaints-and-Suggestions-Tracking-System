<?php
require_once __DIR__ . '/../../db.php';

class AdminAuthService {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
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

        $stmt = $this->db->prepare("
            SELECT admin_id, name, email, phone_number, role, password, is_active
            FROM admin
            WHERE LOWER(email) = LOWER(?)
            LIMIT 1
        ");

        if (!$stmt) {
            error_log("[AdminAuth] Database prepare failed: " . $this->db->error);
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
            $stmt->close();
            return [
                'status' => false,
                'message' => 'Invalid admin credentials',
                'is_admin' => false
            ];
        }

        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin['is_active'] != 1) {
            if ($admin['role'] === 'super_admin') {
                return [
                    'status' => false,
                    'message' => 'Super Admin account is deactivated. Please contact system administrator.',
                    'is_admin' => false
                ];
            }
            
            return [
                'status' => false,
                'message' => 'Your account has been deactivated. Please contact Super Admin.',
                'is_admin' => false
            ];
        }

        $passwordValid = $this->verifyPassword($password, $admin['password']);

        if (!$passwordValid) {
            return [
                'status' => false,
                'message' => 'Invalid admin credentials',
                'is_admin' => false
            ];
        }

        unset($admin['password']);

        $admin_type = ($admin['role'] === 'super_admin') ? 'super_admin' : 'database_admin';
        
        return [
            'status' => true,
            'message' => 'Admin verified',
            'is_admin' => true,
            'data' => $admin,
            'admin_type' => $admin_type
        ];
    }

    private function verifyPassword($inputPassword, $storedPassword) {
        if (substr($storedPassword, 0, 4) === '$2y$' || substr($storedPassword, 0, 4) === '$2a$') {
            return password_verify($inputPassword, $storedPassword);
        }
        if (strlen($storedPassword) === 64 && ctype_xdigit($storedPassword)) {
            return (hash('sha256', $inputPassword) === $storedPassword);
        }
        
        return ($inputPassword === $storedPassword);
    }

    public function isAdminEmail($email) {
        $email = trim($email);

        $stmt = $this->db->prepare("
            SELECT 1 FROM admin 
            WHERE LOWER(email) = LOWER(?) AND is_active = 1
            LIMIT 1
        ");
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }
    public function getAdminContactInfo($email) {
        $stmt = $this->db->prepare("
            SELECT phone_number, email, name, role, is_active 
            FROM admin 
            WHERE LOWER(email) = LOWER(?)
            LIMIT 1
        ");
        
        if (!$stmt) {
            return null;
        }
        
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
                'type' => $admin['role'] === 'super_admin' ? 'super_admin' : 'database_admin'
            ];
        }

        $stmt->close();
        return null;
    }

    public function isSuperAdmin($admin_id) {
        $stmt = $this->db->prepare("
            SELECT role FROM admin WHERE admin_id = ? LIMIT 1
        ");
        
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

    public function getAdminById($admin_id) {
        $stmt = $this->db->prepare("
            SELECT admin_id, name, email, phone_number, role, is_active
            FROM admin
            WHERE admin_id = ?
            LIMIT 1
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
}
?>