<?php
require_once __DIR__ . '/../../db.php';

class StudentService extends Database {
    
    public function loginStudent($student_id, $password) {
        try {
            $connection = $this->getConnection();
            if (!$connection) {
                return ['status' => false, 'message' => 'Database connection error'];
            }

            $student_id = trim($student_id);

            $stmt = $connection->prepare("
                SELECT student_id, first_name, last_name, email, phone_number, password 
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

                $isValidPassword = false;
                
                if (password_verify($password, $student['password'])) {
                    $isValidPassword = true;
                } elseif (hash('sha256', $password) === $student['password']) {
                    $isValidPassword = true;
                }

                if ($isValidPassword) {
                    return [
                        'status' => true,
                        'message' => 'Login successful!',
                        'data' => [
                            'student_id' => $student['student_id'],
                            'first_name' => $student['first_name'],
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
}
?>