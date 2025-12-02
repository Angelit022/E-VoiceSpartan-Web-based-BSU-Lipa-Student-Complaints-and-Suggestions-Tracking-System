<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    exit('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Unauthorized access</div>');
}

require_once '../../db.php';
require_once '../classes/UserProfile.php';

$database = new Database();
$db = $database->getConnection();
$userProfile = new UserProfile($db, $_SESSION['user_id']);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

if (empty($id) || empty($type)) {
    http_response_code(400);
    exit('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Invalid parameters</div>');
}

$result = $userProfile->getResponses($type, $id);

if (!$result['success']) {
    http_response_code(403);
    exit('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ' . htmlspecialchars($result['message']) . '</div>');
}

$responses = $result['responses'];

$submission = $userProfile->getSubmissionDetails(ucfirst($type), $id);

$userProfile->markResponseViewed($type, $id);

$typeLabel = ucfirst($type);
$typeIcon = $type === 'complaint' ? 'bi-exclamation-circle' : 'bi-lightbulb';
$typeBadgeColor = $type === 'complaint' ? '#ff8c42' : '#4a90e2';
?>

<div class="response-viewer-header">
    <h2>
        <i class="bi bi-envelope-open"></i>
        Admin Responses
    </h2>
    <p>
        <i class="bi <?php echo $typeIcon; ?>"></i>
        <span class="badge" style="background: <?php echo $typeBadgeColor; ?>; color: white;">
            <?php echo $typeLabel; ?>
        </span>
        <span class="ms-2">Reference #<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?></span>
        <?php if (!empty($responses)): ?>
            <span class="response-count-badge ms-auto"><?php echo count($responses); ?> Response<?php echo count($responses) !== 1 ? 's' : ''; ?></span>
        <?php endif; ?>
    </p>
    <div class="response-viewer-close" onclick="closeResponseModal()" title="Close (ESC)">
        <i class="bi bi-x"></i>
    </div>
</div>

<div class="response-viewer-body">
    <?php if (empty($responses)): ?>
        <div class="no-responses">
            <i class="bi bi-inbox"></i>
            <h3>No Responses Yet</h3>
            <p>The admin hasn't responded to this submission yet. You'll be notified when they do.</p>
        </div>
    <?php else: ?>
        <?php 
        usort($responses, function($a, $b) {
            return strtotime($b['date_responded']) - strtotime($a['date_responded']);
        });
        
        foreach ($responses as $index => $response): 
            $adminInitials = implode('', array_map(function($n) { return $n[0]; }, explode(' ', $response['admin_name'])));
            $roleLabel = $response['admin_role'] === 'super_admin' ? 'Super Administrator' : 'SSC Administrator';
            $formattedDate = date('F d, Y @ h:i A', strtotime($response['date_responded']));
        ?>
            <div class="response-item" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                <div class="response-admin-info">
                    <div class="response-admin-avatar">
                        <?php echo strtoupper($adminInitials); ?>
                    </div>
                    <div class="response-admin-details">
                        <h4 class="response-admin-name"><?php echo htmlspecialchars($response['admin_name']); ?></h4>
                        <p class="response-admin-role">
                            <i class="bi bi-shield-check"></i>
                            <span class="badge"><?php echo $roleLabel; ?></span>
                        </p>
                    </div>
                </div>
                <div class="response-date">
                    <i class="bi bi-calendar-event"></i>
                    <?php echo $formattedDate; ?>
                </div>
                <div class="response-message"><?php echo nl2br(htmlspecialchars($response['message'])); ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>