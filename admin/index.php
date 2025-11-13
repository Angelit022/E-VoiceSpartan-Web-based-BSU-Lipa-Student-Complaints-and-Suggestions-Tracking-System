<?php
session_start();
require_once '../db.php';
require_once './process/AdminMiddleware.php';
requireAdminAccess();

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = ['dashboard', 'responses', 'feedback', 'profile', 'settings', 'admin-management'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-VoiceSpartan Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/dashboard.css">
    <link rel="stylesheet" href="./css/responses.css">
    <link rel="stylesheet" href="./css/profile.css">
</head>
<body>

    <?php include './includes/navbar.php'; ?>
    
    <div class="d-flex" style="margin-top: 56px;">

        <main class="flex-grow-1 w-100 main-content">
            <div class="p-3 p-md-4">
                <?php
                $page_file = "./pages/{$page}.php";
                if (file_exists($page_file)) {
                    include $page_file;
                } else {
                    include './pages/dashboard.php';
                }
                ?>
            </div>
            
    
            <?php include './includes/footer.php'; ?>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="./js/main.js"></script>
    

    <?php if ($page === 'responses'): ?>
        <script src="./js/responses.js"></script>
    <?php elseif ($page === 'profile'): ?>
        <script src="./js/profile.js"></script>
    <?php elseif ($page === 'settings'): ?>
        <script src="./js/settings.js"></script>
    <?php endif; ?>
</body>
</html>