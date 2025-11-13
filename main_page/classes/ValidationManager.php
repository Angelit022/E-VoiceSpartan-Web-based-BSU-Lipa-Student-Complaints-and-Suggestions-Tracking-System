<?php
/**
 * ValidationManager - Centralized validation for all user inputs
 * Uses prepared statements and sanitization for safety
 */
class ValidationManager {
    private $errors = [];

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

    public function validatePassword($password, $minLength = 6) {
        if (empty($password)) {
            $this->errors[] = "Password is required";
            return false;
        }
        if (strlen($password) < $minLength) {
            $this->errors[] = "Password must be at least $minLength characters";
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

    public function validatePhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 10) {
            $this->errors[] = "Invalid phone number";
            return false;
        }
        return true;
    }

    public function validateName($name, $fieldName = "Name") {
        if (empty($name)) {
            $this->errors[] = "$fieldName is required";
            return false;
        }
        if (strlen($name) > 100) {
            $this->errors[] = "$fieldName is too long";
            return false;
        }
        return true;
    }

    public function validateNotEmpty($value, $fieldName = "Field") {
        if (empty(trim($value))) {
            $this->errors[] = "$fieldName is required";
            return false;
        }
        return true;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function hasErrors() {
        return count($this->errors) > 0;
    }

    public function clearErrors() {
        $this->errors = [];
    }

    public function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
?>