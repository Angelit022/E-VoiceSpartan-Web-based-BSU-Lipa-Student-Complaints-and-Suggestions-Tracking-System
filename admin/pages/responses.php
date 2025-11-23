<?php
require_once '../db.php';
require_once __DIR__ . '/../classes/ResponsesService.php';

// No activity logging for page views

$responsesService = new ResponsesService();
$submissions = $responsesService->getAllSubmissions();
?>

<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-danger"><i class="bi bi-chat-dots"></i> Manage Student Reports</h2>
            <p class="text-muted">View and manage all student complaints and suggestions</p>
        </div>
        <span class="badge bg-danger fs-6"><?php echo count($submissions); ?> Total Submissions</span>
    </div>
    
    <!-- Filter and Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by title, student name, or email...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Filter by Type</label>
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="Complaint">Complaints</option>
                        <option value="Suggestion">Suggestions</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Filter by Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-danger w-100" onclick="resetFilters()">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sorting controls and pagination info -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex justify-content-between align-items-center py-2">
            <div>
                <label class="form-label mb-0 fw-semibold">Sort:</label>
                <div class="btn-group btn-group-sm ms-2" role="group">
                    <button type="button" class="btn btn-outline-danger" onclick="sortTable('asc')" id="sortAsc">
                        <i class="bi bi-arrow-up"></i> Oldest First
                    </button>
                    <button type="button" class="btn btn-outline-danger active" onclick="sortTable('desc')" id="sortDesc">
                        <i class="bi bi-arrow-down"></i> Newest First
                    </button>
                </div>
            </div>
            <div>
                <small class="text-muted">Showing <span id="startRow">1</span> to <span id="endRow">10</span> of <span id="totalRows">0</span> entries</small>
            </div>
        </div>
    </div>
    
    <!-- Reports Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive-custom">
                <table class="table table-hover mb-0" id="reportsTable">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 130px;">Submission ID</th>
                            <th>Student</th>
                            <th>Title</th>
                            <th style="width: 120px;">Category</th>
                            <th style="width: 80px;">Type</th>
                            <th style="width: 100px;">Priority</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 150px;">Date</th>
                            <th style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reportTableBody">
                        <?php foreach ($submissions as $row): ?>
                            <tr data-type="<?php echo htmlspecialchars($row['type']); ?>" 
                                data-status="<?php echo htmlspecialchars($row['status_name']); ?>" 
                                data-date="<?php echo strtotime($row['date_submitted']); ?>">
                                <td><small class="text-muted fw-semibold">#<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></small></td>
                                <td>
                                    <?php if ($row['is_anonymous']): ?>
                                        <i class="bi bi-incognito me-1"></i> <strong>Anonymous</strong>
                                    <?php else: ?>
                                        <strong><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(substr($row['title'], 0, 50)); ?></td>
                                <td><small><?php echo !empty($row['category']) ? htmlspecialchars($row['category']) : 'N/A'; ?></small></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $row['type'] === 'Complaint' ? '#FF8C00' : '#0d6efd'; ?>;">
                                        <?php echo $row['type']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['priority']): ?>
                                        <span class="badge" style="background-color: <?php echo ResponsesService::getPriorityColor($row['priority']); ?>;">
                                            <?php echo $row['priority']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo ResponsesService::getStatusBadgeColor($row['status_name']); ?>;">
                                        <?php echo $row['status_name']; ?>
                                    </span>
                                </td>
                                <td><small class="text-muted"><?php echo date('M d, Y H:i', strtotime($row['date_submitted'])); ?></small></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary" 
                                                onclick='viewDetails(<?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                                                title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success" 
                                                onclick='openResponseModal(<?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                                                title="<?php echo $row['is_anonymous'] ? 'Send Response (Anonymous)' : 'Send Response'; ?>">
                                            <i class="bi bi-chat"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" 
                                                onclick="updateStatus(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['type']); ?>', <?php echo $row['status_id']; ?>)"
                                                title="Update Status">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination controls -->
        <div class="card-footer bg-light d-flex justify-content-between align-items-center py-3 flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 fw-semibold">Rows per page:</label>
                <select class="form-select form-select-sm" style="width: 70px; padding-right: 2rem;" id="rowsPerPage" onchange="changePagination()">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
            <nav aria-label="Table pagination">
                <ul class="pagination mb-0" id="pagination"></ul>
            </nav>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #0d6efd; color: white;">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-text"></i> Submission Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3" id="detailStudentSection">
                    <div class="col-md-6">
                        <strong>Student Name:</strong>
                        <p id="detailStudentName" class="mb-2"></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <p id="detailEmail" class="mb-2"></p>
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Title:</strong>
                    <p id="detailTitle" class="mb-2"></p>
                </div>
                <div class="mb-3">
                    <strong>Category:</strong>
                    <p id="detailCategory" class="mb-2"></p>
                </div>
                <div class="mb-3">
                    <strong>Description:</strong>
                    <p id="detailDescription" class="mb-2"></p>
                </div>
                <div class="mb-3" id="detailAttachmentsSection" style="display: none;">
                    <strong>Attachments:</strong>
                    <div id="detailAttachments" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #28a745; color: white;">
                <h5 class="modal-title fw-bold"><i class="bi bi-reply"></i> Send Response</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form onsubmit="submitResponse(event)" id="responseForm">
                <div class="modal-body">
                    <input type="hidden" id="responseSubmissionId">
                    <input type="hidden" id="responseSubmissionType">
                    <input type="hidden" id="responseStudentId">
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i><strong>Responding to:</strong> <span id="responseStudentName"></span>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Student Email</label>
                        <input type="text" class="form-control" id="responseStudentEmail" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subject</label>
                        <input type="text" class="form-control" id="responseSubject" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Your Response <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="responseMessage" rows="6" required 
                                  maxlength="1000" placeholder="Enter your response message..."></textarea>
                        <small class="text-muted">
                            <span id="charCount">0</span>/1000 characters
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send"></i> Send Response
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #ffc107; color: #000;">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-repeat"></i> Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form onsubmit="confirmStatusUpdate(event)" id="statusForm">
                <div class="modal-body">
                    <input type="hidden" id="statusSubmissionId">
                    <input type="hidden" id="statusSubmissionType">
                    <input type="hidden" id="statusCurrentStatusId">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Status</label>
                        <input type="text" class="form-control" id="currentStatusDisplay" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select New Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="newStatus" required>
                            <option value="">-- Select Status --</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i> 
                        Status transitions follow a one-way flow: <strong>Pending → In Progress → Resolved/Rejected</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>