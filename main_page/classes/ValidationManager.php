<?php
class ValidationManager {
    private $errors = [];

    public function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    public function validateName($name, $fieldName = 'Name') {
        if (empty($name)) {
            $this->errors[] = "$fieldName is required";
            return false;
        }
        
        if (strlen($name) < 2) {
            $this->errors[] = "$fieldName must be at least 2 characters long";
            return false;
        }
        
        if (strlen($name) > 50) {
            $this->errors[] = "$fieldName must not exceed 50 characters";
            return false;
        }
        
        if (!preg_match("/^[a-zA-Z\s'-]+$/", $name)) {
            $this->errors[] = "$fieldName can only contain letters, spaces, hyphens, and apostrophes";
            return false;
        }
        
        return true;
    }

    public function validateEmail($email) {
        if (empty($email)) {
            $this->errors[] = "Email is required";
            return false;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Invalid email format";
            return false;
        }
        
        return true;
    }

    public function validatePhone($phone) {
        // Phone is optional, but if provided must be valid
        if (empty($phone)) {
            return true;
        }
        
        // Remove any non-numeric characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Must be exactly 11 digits
        if (strlen($cleanPhone) !== 11) {
            $this->errors[] = "Phone number must be exactly 11 digits";
            return false;
        }
        
        // Must start with 09
        if (!preg_match('/^09[0-9]{9}$/', $cleanPhone)) {
            $this->errors[] = "Phone number must start with 09 and be 11 digits long (e.g., 09466161074)";
            return false;
        }
        
        return true;
    }

    public function validatePassword($password, $minLength = 1) {
        if (empty($password)) {
            $this->errors[] = "Password is required";
            return false;
        }
        
        if (strlen($password) < $minLength) {
            $this->errors[] = "Password must be at least $minLength characters long";
            return false;
        }
        
        return true;
    }

    public function validatePasswordMatch($password, $confirmPassword) {
        if ($password !== $confirmPassword) {
            $this->errors[] = "Passwords do not match";
            return false;
        }
        
        return true;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function clearErrors() {
        $this->errors = [];
    }

    public function hasErrors() {
        return !empty($this->errors);
    }
}
?>