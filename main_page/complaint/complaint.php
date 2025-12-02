<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: ../../signup_login/login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>File a Complaint - E-VoiceSpartan</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="stylesheet" href="../css/global-theme.css">
  <link rel="stylesheet" href="../css/components.css">
  <link rel="stylesheet" href="../css/forms.css">
  <link rel="stylesheet" href="../css/navbar.css">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <?php include '../components/navbar.php'; ?>

  <main class="form-container">
    <div class="form-header text-center">
      <h1 class="mb-1">File a Complaint</h1>
      <p class="mb-0">Help us improve by reporting issues in our campus</p>
    </div>

    <div class="steps-bar mb-4">
      <div class="step active" id="step1-indicator">
        <div class="step-circle">1</div>
        <div class="step-label">Category</div>
      </div>
      <div class="step" id="step2-indicator">
        <div class="step-circle">2</div>
        <div class="step-label">Details</div>
      </div>
      <div class="step" id="step3-indicator">
        <div class="step-circle">3</div>
        <div class="step-label">Attachments</div>
      </div>
      <div class="step" id="step4-indicator">
        <div class="step-circle">4</div>
        <div class="step-label">Review</div>
      </div>
      <div class="step" id="step5-indicator">
        <div class="step-circle">5</div>
        <div class="step-label">Submitted</div>
      </div>
    </div>

    <form id="complaintForm" class="form-wizard" method="POST" action="process_complaint.php" novalidate enctype="multipart/form-data">
      
      <div class="step-panel active" id="step1">
        <h2>Step 1: Select Category</h2>
        <p>Choose the category that best describes your complaint</p>
        <div class="form-group">
          <label for="category">Complaint Category *</label>
          <select id="category" name="category" required>
            <option value="">-- Select a category --</option>
            <option value="Academic Concerns">Academic Concerns</option>
            <option value="Facilities and Campus Environment">Facilities and Campus Environment</option>
            <option value="Administrative Services">Administrative Services</option>
            <option value="Student Services and Welfare">Student Services and Welfare</option>
            <option value="Technology and Online Systems">Technology and Online Systems</option>
            <option value="Security and Discipline">Security and Discipline</option>
            <option value="Campus Policies and Regulations">Campus Policies and Regulations</option>
            <option value="Accessibility">Accessibility</option>
            <option value="Others">Others</option>
          </select>
        </div>
      </div>

      <div class="step-panel" id="step2">
        <h2>Step 2: Provide Details</h2>
        <p>Share detailed information about the issue</p>
        <div class="form-group">
          <label for="title">Complaint Title *</label>
          <input type="text" id="title" name="title" placeholder="Brief title of your complaint" required>
        </div>
        <div class="form-group">
          <label for="description">Detailed Description *</label>
          <textarea id="description" name="description" placeholder="Describe the issue in detail." required></textarea>
        </div>
        <div class="form-group">
          <label for="priority">Priority Level *</label>
          <select id="priority" name="priority" required>
            <option value="">-- Select Priority Level --</option>
            <option value="Low">Low - General Feedback</option>
            <option value="Medium">Medium - Important Issue</option>
            <option value="High">High - Urgent Issue</option>
          </select>
        </div>
      </div>

      <div class="step-panel" id="step3">
        <h2>Step 3: Add Attachments</h2>
        <p>Upload supporting files as proof (optional)</p>
        <div class="form-group">
          <label for="attachment">Upload File</label>
          <div class="file-upload-area" id="fileUploadArea">
            <i class="bi bi-cloud-arrow-up" style="font-size: 2.5rem; color: var(--color-red); margin-bottom: 0.5rem; display: block;"></i>
            <p style="margin: 0.5rem 0; color: var(--color-black); font-weight: 600;">Drag and drop your file here</p>
            <p style="margin: 0; color: var(--color-gray); font-size: 0.875rem;">or click to select a file</p>
            <p style="margin: 0.5rem 0 0 0; color: var(--color-gray); font-size: 0.75rem;">Supported: JPG, PNG, MP4, PDF, DOC</p>
            <input type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.mp4,.pdf,.doc,.docx">
          </div>
          <div id="fileList" class="file-list"></div>
        </div>
        <p style="font-size: 0.875rem; color: var(--color-gray); margin-top: 1rem;">
          <i class="bi bi-info-circle"></i> Maximum file size: 10MB. This step is optional.
        </p>
      </div>

      <div class="step-panel" id="step4">
        <h2>Step 4: Review & Submit</h2>
        <p>Please verify all details before submitting your complaint</p>

        <div class="review-section">
          <div style="margin-bottom: 1rem; font-weight: 600; color: var(--color-black);">Your Complaint Information:</div>
          <div class="review-item"><span class="review-label">Category:</span><span class="review-value" id="reviewCategory">-</span></div>
          <div class="review-item"><span class="review-label">Priority Level:</span><span class="review-value" id="reviewPriority">-</span></div>
          <div class="review-item"><span class="review-label">Title:</span><span class="review-value" id="reviewTitle">-</span></div>
          <div class="review-item"><span class="review-label">Description:</span><span class="review-value" id="reviewDescription">-</span></div>
          <div class="review-item"><span class="review-label">Attachment:</span><span class="review-value" id="reviewAttachment">None</span></div>
        </div>

        <div style="margin: 1.5rem 0;">
          <input type="checkbox" id="anonymous" name="anonymous" class="toggle-checkbox">
          <label for="anonymous" class="toggle-label">
            <i class="bi bi-incognito anonymous-icon"></i>
            <span>File this complaint anonymously</span>
            <div class="toggle-switch"></div>
          </label>
          <div class="anonymous-info" id="anonymousInfo" style="display: none;">
            <i class="bi bi-info-circle"></i>
            <p>Your name and contact information will not be visible to administrators.</p>
          </div>
        </div>

        <div class="terms-section">
          <div class="terms-header">
            <i class="bi bi-file-earmark-text terms-icon"></i>
            <span>Terms & Conditions</span>
          </div>
          <div class="terms-container">
            <strong>By submitting this complaint, you agree to the following:</strong>
            <ul>
              <li>The information provided is accurate and truthful</li>
              <li>No false or misleading statements were made</li>
              <li>False complaints may result in disciplinary action</li>
              <li>Your complaint will be reviewed according to university policies</li>
              <li>You grant permission to share details with relevant departments</li>
              <li>You have read and understood these terms</li>
            </ul>
          </div>
          <div class="terms-checkbox-wrapper">
            <input type="checkbox" id="agree" name="agree" required>
            <label for="agree"><span class="checkbox-custom"></span>I agree to the terms and conditions</label>
          </div>
        </div>
      </div>

      <div class="step-panel" id="step5" style="display: none;">
        <div class="success-message">
          <div class="success-icon"><i class="bi bi-check-circle" style="font-size: 3rem; color: var(--color-green);"></i></div>
          <h3 style="margin-top: 1rem;">Complaint Submitted Successfully!</h3>
          <p style="margin-bottom: 2rem;">Thank you for your submission. We will review it and keep you updated.</p>
          <div class="anonymous-notification" id="step5AnonymousAlert" style="display: none;">
            <i class="bi bi-incognito"></i>
            <div>
              <p style="margin: 0; font-weight: 600;">Submitted Anonymously</p>
              <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">Your name and contact information are hidden.</p>
            </div>
          </div>
          <div style="background: var(--color-gray-light); border: 2px solid var(--color-red); border-radius: 0.5rem; padding: 1.5rem; margin: 1.5rem 0; text-align: center;">
            <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem; color: var(--color-gray);">Your Reference ID:</p>
            <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--color-red);" id="referenceId">-</p>
          </div>
          <p style="margin-top: 1rem; color: var(--color-gray);">Save this ID to track your complaint status.</p>
        </div>
      </div>

      <div class="form-actions">
        <button type="button" id="prevBtn" class="btn btn-back">Back</button>
        <button type="button" id="nextBtn" class="btn btn-next">Next</button>
        <button type="button" id="submitBtn" class="btn btn-success" style="display: none;"><i class="bi bi-check-circle"></i> Submit Complaint</button>
        <button type="button" id="doneBtn" class="btn btn-success" style="display: none;">Done</button>
      </div>
    </form>
  </main>

  <?php include '../components/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/complaint_form.js"></script>
  <script src="../js/navbar.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const complaintForm = window.complaintForm;
      <?php
      if (isset($_SESSION['complaint_success'])) {
          $complaint_id = $_SESSION['complaint_insert_id'] ?? '';
          $is_anonymous = isset($_SESSION['complaint_is_anonymous']) && $_SESSION['complaint_is_anonymous'] ? 'true' : 'false';
          echo "complaintForm.showSuccessStep('{$complaint_id}', {$is_anonymous});";
          unset($_SESSION['complaint_success'], $_SESSION['complaint_insert_id'], $_SESSION['complaint_is_anonymous']);
      }
      ?>
    });
  </script>
</body>
</html>