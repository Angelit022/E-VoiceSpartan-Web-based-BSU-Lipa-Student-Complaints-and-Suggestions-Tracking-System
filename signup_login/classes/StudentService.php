<?php
require_once __DIR__ . '/../../db.php';

class StudentService extends Database {
    
    public function registerStudent($first_name, $middle_initial, $last_name, $email, $student_id, $phone_number, $password) {
        try {
            $connection = $this->getConnection();
            if (!$connection) {
                return ['status' => false, 'message' => 'Database connection error'];
            }

            $validation = $this->validateStudentData($first_name, $middle_initial, $last_name, $email, $student_id, $phone_number, $password, $connection);
            if ($validation['status'] === false) {
                return $validation;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $connection->prepare("INSERT INTO student (first_name, middle_initial, last_name, email, student_id, phone_number, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                return ['status' => false, 'message' => 'Database error'];
            }

            $stmt->bind_param('sssssss', $first_name, $middle_initial, $last_name, $email, $student_id, $phone_number, $hashedPassword);
            
            if (!$stmt->execute()) {
                $errorMsg = $stmt->error;
                
                if (strpos($errorMsg, 'Duplicate entry') !== false) {
                    if (strpos($errorMsg, "student_id") !== false) {
                        $stmt->close();
                        return ['status' => false, 'message' => 'Student ID already exists'];
                    } elseif (strpos($errorMsg, "email") !== false) {
                        $stmt->close();
                        return ['status' => false, 'message' => 'Email already registered'];
                    }
                }
                
                $stmt->close();
                return ['status' => false, 'message' => 'Error registering student: ' . $errorMsg];
            }

            $stmt->close();
            return ['status' => true, 'message' => 'Student registered successfully!', 'student_id' => $student_id];

        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Registration error'];
        }
    }

    public function loginStudent($student_id, $password) {
        try {
            $connection = $this->getConnection();
            if (!$connection) {
                return ['status' => false, 'message' => 'Database connection error'];
            }

            $student_id = trim($student_id);

            $stmt = $connection->prepare("
                SELECT student_id, first_name, middle_initial, last_name, email, phone_number, password 
                FROM student 
                WHERE student_id = ? 
                LIMIT 1
            ");

            if (!$stmt) {
                return ['status' => false, 'message' => 'Database error'];
            }

            $stmt->bind_param('s', $student_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $student = $result->fetch_assoc();
                $stmt->close();

                if (password_verify($password, $student['password'])) {
                    return [
                        'status' => true,
                        'message' => 'Login successful!',
                        'data' => [
                            'student_id' => $student['student_id'],
                            'first_name' => $student['first_name'],
                            'middle_initial' => $student['middle_initial'],
                            'last_name' => $student['last_name'],
                            'email' => $student['email'],
                            'phone_number' => $student['phone_number'],
                            'role' => 'student'
                        ]
                    ];
                } else {
                    return ['status' => false, 'message' => 'Incorrect password'];
                }
            } else {
                $stmt->close();
                return ['status' => false, 'message' => 'Student ID not found'];
            }

        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Login error. Please try again later.'];
        }
    }

    private function validateStudentData($first_name, $middle_initial, $last_name, $email, $student_id, $phone_number, $password, $connection) {
        
        $first_name = trim($first_name);
        $middle_initial = trim($middle_initial);
        $last_name = trim($last_name);
        $email = trim(strtolower($email));
        $student_id = trim($student_id); 
        $phone_number = trim(preg_replace('/\s+/', '', $phone_number));
        
        // Validate first name
        if (empty($first_name) || strlen($first_name) < 2) {
            return ['status' => false, 'message' => 'First name must be at least 2 characters'];
        }
        
        // Validate last name
        if (empty($last_name) || strlen($last_name) < 2) {
            return ['status' => false, 'message' => 'Last name must be at least 2 characters'];
        }
        
        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => false, 'message' => 'Please enter a valid email address'];
        }

        // Ensure only GSuite email is used (e.g., @g.batstate-u.edu.ph)
        $allowedDomain = '@g.batstate-u.edu.ph';
        if (!str_ends_with(strtolower($email), $allowedDomain)) {
            return [
                'status' => false,
                'message' => 'Only BSU GSuite accounts (e.g., yourname' . $allowedDomain . ') are allowed for registration.'
            ];
        }

        // Validate student ID
        if (empty($student_id) || strlen($student_id) < 3) {
            return ['status' => false, 'message' => 'Student ID must be at least 3 characters'];
        }
        
        // Validate phone number
        if (empty($phone_number) || strlen($phone_number) < 10) {
            return ['status' => false, 'message' => 'Phone number must be at least 10 characters'];
        }
        
        // Validate password
        if (empty($password) || strlen($password) < 5) {
            return ['status' => false, 'message' => 'Password must be at least 5 characters'];
        }
        
        $stmt = $connection->prepare("SELECT student_id FROM student WHERE student_id = ? LIMIT 1");
        if (!$stmt) {
            return ['status' => false, 'message' => 'Database error during validation'];
        }
        $stmt->bind_param('s', $student_id);
        if (!$stmt->execute()) {
            $stmt->close();
            return ['status' => false, 'message' => 'Database error during validation'];
        }
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            return ['status' => false, 'message' => 'Student ID already exists'];
        }
        
        // Check if email already exists
        $stmt = $connection->prepare("SELECT email FROM student WHERE LOWER(email) = LOWER(?) LIMIT 1");
        if (!$stmt) {
            return ['status' => false, 'message' => 'Database error during validation'];
        }
        $stmt->bind_param('s', $email);
        if (!$stmt->execute()) {
            $stmt->close();
            return ['status' => false, 'message' => 'Database error during validation'];
        }
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            return ['status' => false, 'message' => 'Email already registered'];
        }
        return ['status' => true, 'message' => 'Validation passed'];
    }
    
}
?>
