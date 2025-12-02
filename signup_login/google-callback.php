<?php
session_start();

try {
    require_once __DIR__ . '/classes/GoogleAuthService.php';

    if (!isset($_GET['code'])) {
        $_SESSION['signup_error'] = 'No authorization code received';
        header('Location: signup.php');
        exit;
    }

    $googleAuth = new GoogleAuthService();
    $result = $googleAuth->handleCallback($_GET['code']);
    
    if ($result['status']) {
        $_SESSION['google_signup_data'] = $result['data'];
        $_SESSION['signup_step'] = 'mobile';
        
        header('Location: signup.php');
        exit;
    } else {
        $_SESSION['signup_error'] = $result['message'];
        header('Location: signup.php');
        exit;
    }

} catch (Exception $e) {
    $_SESSION['signup_error'] = 'An error occurred during authentication. Please try again.';
    header('Location: signup.php');
    exit;
}
?>