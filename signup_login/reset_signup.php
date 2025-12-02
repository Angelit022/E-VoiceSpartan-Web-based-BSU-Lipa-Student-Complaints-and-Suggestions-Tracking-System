<?php
session_start();
unset($_SESSION['google_signup_data']);
unset($_SESSION['signup_step']);
unset($_SESSION['signup_error']);
echo json_encode(['status' => true]);
?>