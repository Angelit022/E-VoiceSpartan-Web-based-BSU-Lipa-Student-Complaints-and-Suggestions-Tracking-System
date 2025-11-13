<?php
session_start();
session_destroy();
header('Location: ../signup_login/login.php');
exit;
?>
