<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: ../../signup_login/login.php");
  exit();
}

require_once '../../db.php';
require_once '../classes/UserProfile.php';

$database = new Database();
$db = $database->getConnection();
$userProfile = new UserProfile($db, $_SESSION['user_id']);

$userInfo = $userProfile->getUserInfo();
$submissions = $userProfile->getAllSubmissions();
$stats = $userProfile->getStatistics();

$fullName = ucfirst($userInfo['first_name']) . ' ' . ucfirst($userInfo['last_name']);
$email = $userInfo['email'];
$joinDate = isset($userInfo['date_joined']) ? $userInfo['date_joined'] : date('Y-m-d');

// Check if we need to auto-open response viewer
$autoOpenResponse = isset($_GET['view_response']) && $_GET['view_response'] == '1';
$responseId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$responseType = isset($_GET['type']) ? $_GET['type'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - E-VoiceSpartan</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://unpkg.com/bootstrap-table@1.22.3/dist/bootstrap-table.min.css" rel="stylesheet">

  <link rel="stylesheet" href="../css/global-theme.css">
  <link rel="stylesheet" href="../css/components.css">
  <link rel="stylesheet" href="../css/profile.css">
  <link rel="stylesheet" href="../css/edit-submission.css">
  <link rel="stylesheet" href="../css/navbar.css">
  <link rel="stylesheet" href="../css/response-viewer.css">
  <link rel="stylesheet" href="../css/feedback-viewer.css">
</head>
<body>
  <?php include '../components/navbar.php'; ?>

  <header class="profile-header" role="banner">
    <div class="profile-header-container">
      <div class="profile-header-content">
        <div class="profile-avatar-section">
          <div class="profile-avatar">
            <i class="bi bi-person-circle"></i>
          </div>
        </div>
        <div class="profile-info-section">
          <h1 class="profile-name"><?php echo htmlspecialchars($fullName); ?></h1>
          <p class="profile-email">
            <i class="bi bi-envelope-fill"></i>
            <?php echo htmlspecialchars($email); ?>
          </p>
          <p class="profile-joined">
            <i class="bi bi-calendar-check-fill"></i>
            Member since <?php echo date('F Y', strtotime($joinDate)); ?>
          </p>
        </div>
      </div>
    </div>
  </header>

  <main class="container-fluid py-4 py-md-5">
    <div class="row mb-4 mb-md-5 g-2 g-md-3">
      <div class="col-6 col-sm-6 col-lg-2">
        <div class="stat-card stat-complaints p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="stat-label small">Complaints</div>
              <div class="stat-value fs-4"><?php echo $stats['total_complaints']; ?></div>
            </div>
            <div class="stat-icon bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center rounded">
              <i class="bi bi-exclamation-circle"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-sm-6 col-lg-2">
        <div class="stat-card stat-suggestions p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="stat-label small">Suggestions</div>
              <div class="stat-value fs-4"><?php echo $stats['total_suggestions']; ?></div>
            </div>
            <div class="stat-icon bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center rounded">
              <i class="bi bi-lightbulb"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-sm-6 col-lg-2">
        <div class="stat-card stat-resolved p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="stat-label small">Resolved</div>
              <div class="stat-value fs-4"><?php echo $stats['resolved']; ?></div>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center rounded">
              <i class="bi bi-check-circle"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-sm-6 col-lg-2">
        <div class="stat-card stat-pending p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="stat-label small">Pending</div>
              <div class="stat-value fs-4"><?php echo $stats['pending']; ?></div>
            </div>
            <div class="stat-icon bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center rounded">
              <i class="bi bi-hourglass-split"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-sm-6 col-lg-2">
        <div class="stat-card stat-in-progress p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="stat-label small">In Progress</div>
              <div class="stat-value fs-4 text-primary"><?php echo $stats['in_progress']; ?></div>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center rounded">
              <i class="bi bi-arrow-repeat"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-sm-6 col-lg-2">
        <div class="stat-card stat-rejected p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="stat-label small">Rejected</div>
              <div class="stat-value fs-4 text-secondary"><?php echo $stats['rejected']; ?></div>
            </div>
            <div class="stat-icon bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center rounded">
              <i class="bi bi-x-circle"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="submissions-section card border-0 shadow-sm">
      <div class="card-body p-4 p-md-5">
        <div class="mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
          <div>
            <h2 class="card-title mb-0">
              <i class="bi bi-list-check"></i> Your Submissions
            </h2>
            <p class="text-muted mb-0">Track and manage all your complaints and suggestions</p>
          </div>
          <div class="ms-md-auto" style="max-width: 320px; width: 100%;">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
              <input type="text" id="tableSearch" class="form-control" placeholder="Search submissions...">
            </div>
          </div>
        </div>

        <div class="filter-buttons mb-4">
          <div class="btn-group d-flex flex-wrap gap-2" role="group">
            <button type="button" class="btn btn-sm btn-outline-danger filter-btn active" data-filter="all" data-filter-type="general"><i class="bi bi-funnel"></i> All</button>
            <button type="button" class="btn btn-sm btn-outline-danger filter-btn" data-filter="Complaint" data-filter-type="type"><i class="bi bi-exclamation-circle"></i> Complaints</button>
            <button type="button" class="btn btn-sm btn-outline-warning filter-btn" data-filter="Suggestion" data-filter-type="type"><i class="bi bi-lightbulb"></i> Suggestions</button>
            <button type="button" class="btn btn-sm btn-outline-info filter-btn" data-filter="Pending" data-filter-type="status"><i class="bi bi-hourglass-split"></i> Pending</button>
            <button type="button" class="btn btn-sm btn-outline-primary filter-btn" data-filter="In Progress" data-filter-type="status"><i class="bi bi-arrow-repeat"></i> In Progress</button>
            <button type="button" class="btn btn-sm btn-outline-success filter-btn" data-filter="Resolved" data-filter-type="status"><i class="bi bi-check-circle"></i> Resolved</button>
            <button type="button" class="btn btn-sm btn-outline-secondary filter-btn" data-filter="Rejected" data-filter-type="status"><i class="bi bi-x-circle"></i> Rejected</button>
          </div>
        </div>

        <div class="table-responsive">
          <?php if (count($submissions) > 0): ?>
            <table class="table table-hover" id="submissionsTable"
                   data-toggle="table"
                   data-pagination="true"
                   data-page-size="10"
                   data-page-list="[10,25,50,100,All]"
                   data-sortable="true">
              <thead class="table-light">
                <tr>
                  <th>Type</th>
                  <th>Title & ID</th>
                  <th class="d-none d-sm-table-cell">Category</th>
                  <th class="d-none d-md-table-cell">Priority</th>
                  <th class="d-none d-md-table-cell">Date</th>
                  <th class="d-none d-lg-table-cell">Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="submissions-body">
              <?php foreach ($submissions as $sub): 
                // Check if submission has responses
                $hasResponses = false;
                if (in_array($sub['status'], ['In Progress', 'Resolved', 'Rejected'])) {
                  $submissionType = strtolower($sub['type']);
                  $idColumn = $submissionType === 'complaint' ? 'complaint_id' : 'suggestion_id';
                  
                  $checkResponseQuery = "SELECT COUNT(*) as count FROM response WHERE $idColumn = ?";
                  $checkStmt = $db->prepare($checkResponseQuery);
                  $checkStmt->bind_param("i", $sub['id']);
                  $checkStmt->execute();
                  $responseResult = $checkStmt->get_result();
                  $responseCount = $responseResult->fetch_assoc();
                  $checkStmt->close();
                  
                  $hasResponses = $responseCount['count'] > 0;
                }
              ?>
                <tr class="submission-row" data-type="<?php echo $sub['type']; ?>" data-status="<?php echo $sub['status']; ?>" data-id="<?php echo $sub['id']; ?>">
                  <td>
                    <span class="badge <?php echo $sub['type'] === 'Complaint' ? 'bg-opacity-75' : 'bg-warning'; ?>" style="<?php echo $sub['type'] === 'Complaint' ? 'background: linear-gradient(135deg, #ff8c42 0%, #ff6b35 100%) !important; color: white;' : ''; ?>">
                      <i class="bi <?php echo $sub['type'] === 'Complaint' ? 'bi-exclamation-circle' : 'bi-lightbulb'; ?>"></i>
                      <?php echo $sub['type']; ?>
                    </span>
                    <?php if (!empty($sub['is_anonymous']) && intval($sub['is_anonymous']) === 1): ?>
                      <span class="badge bg-secondary" title="Submitted anonymously"><i class="bi bi-shield-lock"></i></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="fw-bold"><?php echo htmlspecialchars($sub['title']); ?></div>
                    <div class="text-muted small">#<?php echo str_pad($sub['id'], 5, '0', STR_PAD_LEFT); ?></div>
                  </td>
                  <td class="d-none d-sm-table-cell"><?php echo htmlspecialchars($sub['category'] ?? 'N/A'); ?></td>
                  <td class="d-none d-md-table-cell">
                    <?php if (!empty($sub['priority'])): ?>
                      <span class="badge <?php echo UserProfile::getPriorityBadgeClass($sub['priority']); ?>"><?php echo $sub['priority']; ?></span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td class="d-none d-md-table-cell text-muted"><?php echo date('M d, Y', strtotime($sub['date_submitted'])); ?></td>
                  <td class="d-none d-lg-table-cell">
                    <span class="badge <?php echo UserProfile::getStatusBadgeClass($sub['status']); ?> bg-opacity-75"><?php echo $sub['status']; ?></span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm" role="group">
                      <button class="btn btn-outline-primary" type="button" onclick="viewSubmission(<?php echo $sub['id']; ?>, '<?php echo $sub['type']; ?>')">
                        <i class="bi bi-eye"></i> 
                      </button>
                      <?php if ($sub['status'] === 'Pending'): ?>
                        <button class="btn btn-outline-orange" type="button" onclick="editSubmission(<?php echo $sub['id']; ?>, '<?php echo $sub['type']; ?>')">
                          <i class="bi bi-pencil"></i> 
                        </button>
                        <button class="btn btn-outline-secondary" type="button" onclick="deleteSubmission(<?php echo $sub['id']; ?>, '<?php echo $sub['type']; ?>')" title="Delete submission">
                          <i class="bi bi-trash"></i> 
                        </button>
                      <?php elseif ($sub['status'] === 'Resolved'): ?>
                        <?php if ($hasResponses): ?>
                          <button class="btn-view-response btn btn-outline-success unread" type="button" onclick="viewResponses(<?php echo $sub['id']; ?>, '<?php echo strtolower($sub['type']); ?>')">
                            <i class="bi bi-envelope-open"></i>
                          </button>
                        <?php endif; ?>
                        <button class="btn btn-outline-warning btn-feedback" type="button" onclick="openFeedbackModal(<?php echo $sub['id']; ?>, '<?php echo strtolower($sub['type']); ?>', '<?php echo htmlspecialchars(addslashes($sub['title'])); ?>')" title="Rate your experience">
                          <i class="bi bi-star-fill"></i>
                        </button>
                      <?php elseif ($hasResponses): ?>
                        <button class="btn-view-response btn btn-outline-success unread" type="button" onclick="viewResponses(<?php echo $sub['id']; ?>, '<?php echo strtolower($sub['type']); ?>')">
                          <i class="bi bi-envelope-open"></i>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="text-center py-5">
              <i class="bi bi-inbox" style="font-size: 3rem; color: var(--color-gray-light);"></i>
              <p class="mt-3 mb-1"><strong>No submissions yet.</strong></p>
              <p class="text-muted">Start by filing a complaint or suggestion to help us improve.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-bottom">
          <h5 class="modal-title"><i class="bi bi-file-earmark-text"></i> Submission Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="viewModalBody"></div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-bottom">
          <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Submission</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="editModalBody"></div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://unpkg.com/bootstrap-table@1.22.3/dist/bootstrap-table.min.js"></script>

  <script src="../js/edit-submission.js"></script>
  <script src="../js/response-viewer.js"></script>
  <script src="../js/feedback-viewer.js"></script>
  <script src="../js/profile.js"></script>
  <script src="../js/navbar.js"></script>
  <script src="../js/global-theme.js"></script>

  <?php if ($autoOpenResponse && $responseId > 0 && !empty($responseType)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(function() {
        viewResponses(<?php echo $responseId; ?>, '<?php echo $responseType; ?>');
      }, 500);
    });
  </script>
  <?php endif; ?>

  <?php include '../components/footer.php'; ?>
</body>
</html>