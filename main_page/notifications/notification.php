<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../signup_login/login.php");
    exit();
}

require_once '../../db.php';
require_once '../classes/NotificationManager.php';

$notificationManager = new NotificationManager($_SESSION['user_id']);

// Pagination settings
$notificationsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $notificationsPerPage;

// Get notifications with pagination
$notifications = $notificationManager->getNotifications($notificationsPerPage, $offset);
$totalNotifications = $notificationManager->getTotalCount();
$totalPages = ceil($totalNotifications / $notificationsPerPage);
$unreadCount = $notificationManager->getUnreadCount();

// Helper function to determine notification data attributes
function getNotificationAttributes($notif) {
    $type = $notif['type'];
    
    // Determine related entity (complaint or suggestion)
    if (!empty($notif['complaint_id'])) {
        $related = 'complaint';
    } elseif (!empty($notif['suggestion_id'])) {
        $related = 'suggestion';
    } else {
        $related = 'general';
    }
    
    return "data-type=\"{$type}\" data-related=\"{$related}\"";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - E-VoiceSpartan</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <link rel="stylesheet" href="../css/global-theme.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/notification.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <div class="notifications-container">
        <div class="notifications-header">
            <div>
                <h1>
                    <i class="bi bi-bell-fill"></i>
                    Notifications
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge-unread"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </h1>
                <p style="margin: 0.5rem 0 0 0; color: var(--color-gray); font-weight: 500;">
                    Stay updated with your complaints and suggestions
                </p>
            </div>
            <div class="notifications-actions">
                <?php if ($unreadCount > 0): ?>
                    <button class="btn-action" onclick="markAllAsRead()">
                        <i class="bi bi-check-all"></i> Mark All as Read
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h3>No Notifications</h3>
                <p>You don't have any notifications yet. Check back later!</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="notification-item <?= $notif['is_read'] ? '' : 'unread' ?>" <?= getNotificationAttributes($notif) ?>>
                    <?php if ($notif['type'] === 'response'): ?>
                        <div class="view-response-icon" 
                             onclick="viewResponse(<?= $notif['complaint_id'] ?? $notif['suggestion_id'] ?>, '<?= $notif['complaint_id'] ? 'complaint' : 'suggestion' ?>')" 
                             title="View Response">
                            <i class="bi bi-envelope-open"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="notification-header">
                        <div>
                            <span class="notification-type">
                                <?php 
                                // Display more descriptive type labels
                                $typeLabel = $notif['type'];
                                if ($notif['type'] === 'status_update') {
                                    $typeLabel = 'Status Update';
                                } elseif ($notif['type'] === 'response') {
                                    $typeLabel = 'Admin Response';
                                }
                                echo htmlspecialchars($typeLabel);
                                ?>
                            </span>
                        </div>
                        <span class="notification-time">
                            <i class="bi bi-clock"></i>
                            <?= date('M d, Y h:i A', strtotime($notif['created_at'])) ?>
                        </span>
                    </div>
                    <p class="notification-message">
                        <?= htmlspecialchars($notif['message']) ?>
                    </p>
                    <div class="notification-actions">
                        <?php if (!$notif['is_read']): ?>
                            <button class="notification-btn" onclick="markAsRead(<?= $notif['notification_id'] ?>)">
                                <i class="bi bi-check-circle"></i> Mark as Read
                            </button>
                        <?php endif; ?>
                        <button class="notification-btn" onclick="deleteNotification(<?= $notif['notification_id'] ?>)">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($totalPages > 1): ?>
                <div class="pagination-container">
                    <button class="pagination-btn" 
                            onclick="window.location.href='?page=<?= max(1, $currentPage - 1) ?>'" 
                            <?= $currentPage <= 1 ? 'disabled' : '' ?>>
                        <i class="bi bi-chevron-left"></i> Previous
                    </button>

                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++): 
                    ?>
                        <button class="pagination-btn <?= $i === $currentPage ? 'active' : '' ?>" 
                                onclick="window.location.href='?page=<?= $i ?>'">
                            <?= $i ?>
                        </button>
                    <?php endfor; ?>
                    <button class="pagination-btn" 
                            onclick="window.location.href='?page=<?= min($totalPages, $currentPage + 1) ?>'" 
                            <?= $currentPage >= $totalPages ? 'disabled' : '' ?>>
                        Next <i class="bi bi-chevron-right"></i>
                    </button>
                </div>

                <div class="pagination-info" style="text-align: center; margin-top: 1rem;">
                    Showing <?= $offset + 1 ?> to <?= min($offset + $notificationsPerPage, $totalNotifications) ?> of <?= $totalNotifications ?> notifications
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="../js/notification.js"></script>
    <script src="../js/navbar.js"></script>
    <script src="../js/global-theme.js"></script>
</body>
</html>