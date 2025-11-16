<?php
class SuperAdminAccount {
    private $admin_id = 1;
    private $name = 'Super Administrator';
    private $email = 'bot01200501@gmail.com';
    private $phone_number = '09943081565';
    private $role = 'super_admin';
    private $password = 'admin123';

    public function verifyCredentials($email, $password) {
        if (strtolower($email) !== strtolower($this->email)) {
            return false;
        }

        if ($password !== $this->password) {
            return false;
        }

        // Return admin data (never expose password)
        return [
            'admin_id' => $this->admin_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'role' => $this->role
        ];
    }


    public function getAdminId() {
        return $this->admin_id;
    }

    public function getRole() {
        return $this->role;
    }

    public function getPhoneNumber() {
        return $this->phone_number;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getName() {
        return $this->name;
    }

    public function isAdminEmail($email) {
        return strtolower($email) === strtolower($this->email);
    }
}
?>
