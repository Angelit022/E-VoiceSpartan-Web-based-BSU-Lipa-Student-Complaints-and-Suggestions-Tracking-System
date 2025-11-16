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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Share a Suggestion - E-VoiceSpartan</title>

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
      <h1 class="mb-1">Share a Suggestion</h1>
      <p class="mb-0">Help us improve BSU Lipa campus with your valuable ideas and feedback</p>
    </div>

    <div class="steps-bar mb-4">
      <div class="step active" id="step1-indicator">
        <div class="step-circle">1</div>
        <div class="step-label">Area</div>
      </div>
      <div class="step" id="step2-indicator">
        <div class="step-circle">2</div>
        <div class="step-label">Details</div>
      </div>
      <div class="step" id="step3-indicator">
        <div class="step-circle">3</div>
        <div class="step-label">Review</div>
      </div>
      <div class="step" id="step4-indicator">
        <div class="step-circle">4</div>
        <div class="step-label">Submitted</div>
      </div>
    </div>

    <form id="suggestionForm" class="form-wizard" method="POST" action="process_suggestion.php" novalidate enctype="multipart/form-data">
      
      <div class="step-panel active" id="step1">
        <h2>Step 1: Select Improvement Area</h2>
        <p>Choose the area you'd like to improve</p>

        <div class="form-group">
          <label>Improvement Area *</label>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
            <label class="area-option"><input type="radio" name="area" value="Academic Programs & Curriculum" required><span>Academic Programs & Curriculum</span></label>
            <label class="area-option"><input type="radio" name="area" value="Campus Facilities & Infrastructure" required><span>Campus Facilities & Infrastructure</span></label>
            <label class="area-option"><input type="radio" name="area" value="Student Services & Support" required><span>Student Services & Support</span></label>
            <label class="area-option"><input type="radio" name="area" value="Library & Learning Resources" required><span>Library & Learning Resources</span></label>
            <label class="area-option"><input type="radio" name="area" value="Technology & IT Services" required><span>Technology & IT Services</span></label>
            <label class="area-option"><input type="radio" name="area" value="Campus Safety & Security" required><span>Campus Safety & Security</span></label>
            <label class="area-option"><input type="radio" name="area" value="Student Life & Activities" required><span>Student Life & Activities</span></label>
            <label class="area-option"><input type="radio" name="area" value="Dining & Cafeteria Services" required><span>Dining & Cafeteria Services</span></label>
            <label class="area-option"><input type="radio" name="area" value="Transportation & Parking" required><span>Transportation & Parking</span></label>
            <label class="area-option"><input type="radio" name="area" value="Other" required><span>Other</span></label>
          </div>
        </div>
      </div>

      <div class="step-panel" id="step2">
        <h2>Step 2: Provide Details</h2>
        <p>Share the details of your suggestion</p>

        <div class="form-group">
          <label for="title">Suggestion Title *</label>
          <input type="text" id="title" name="title" placeholder="Brief title of your suggestion" required>
        </div>

        <div class="form-group">
          <label for="priority">Priority Level *</label>
          <select id="priority" name="priority" class="form-select" required>
            <option value=""selected>-- Select Priority Level --</option>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
          </select>
        </div>

        <div class="form-group">
          <label for="description">Detailed Description *</label>
          <textarea id="description" name="description" placeholder="Provide as much detail as possible to help us understand your vision" required></textarea>
        </div>
      </div>

      <div class="step-panel" id="step3">
        <h2>Step 3: Review & Submit</h2>
        <p>Please verify all details before submitting</p>

        <div class="review-section">
          <div style="margin-bottom: 1rem; font-weight: 600;">Your Suggestion Information:</div>
          <div class="review-item"><span class="review-label">Area:</span><span class="review-value" id="reviewArea">-</span></div>
          <div class="review-item"><span class="review-label">Title:</span><span class="review-value" id="reviewTitle">-</span></div>
          <div class="review-item"><span class="review-label">Priority:</span><span class="review-value" id="reviewPriority">-</span></div>
          <div class="review-item"><span class="review-label">Description:</span><span class="review-value" id="reviewDescription">-</span></div>
        </div>

        <div style="margin: 1.5rem 0;">
          <input type="checkbox" id="anonymous" name="anonymous" class="toggle-checkbox">
          <label for="anonymous" class="toggle-label">
            <i class="bi bi-incognito anonymous-icon"></i>
            <span>Submit this suggestion anonymously</span>
            <div class="toggle-switch"></div>
          </label>
          <div class="anonymous-info" id="anonymousInfo" style="display: none; flex-direction: row; align-items: flex-start; gap: 0.75rem; margin-top: 1rem; padding: 0.75rem 1rem; background: var(--color-blue-light); border-left: 4px solid var(--color-blue); border-radius: 0.5rem;">
            <i class="bi bi-info-circle" style="margin-top: 0.25rem;"></i>
            <p style="margin: 0; font-size: 0.875rem;">When anonymous, your name and contact information will not be visible to administrators.</p>
          </div>
        </div>

        <div class="terms-section">
          <div class="terms-header">
            <i class="bi bi-file-earmark-text terms-icon"></i>
            <span>Terms & Conditions</span>
          </div>
          <div class="terms-container">
            <strong>By submitting this suggestion, you agree to the following:</strong>
            <ul>
              <li>The information provided is accurate and truthful</li>
              <li>Your suggestion is constructive and intended to improve BSU Lipa campus</li>
              <li>You grant the university permission to share your suggestion with relevant departments</li>
              <li>You acknowledge that you have read and understood these terms</li>
            </ul>
          </div>
          <div class="terms-checkbox-wrapper">
            <input type="checkbox" id="agree" name="agree" required>
            <label for="agree"><span class="checkbox-custom"></span>I agree to the terms and conditions stated above</label>
          </div>
        </div>
      </div>

      <div class="step-panel" id="step4" style="display: none;">
        <div class="success-message">
          <div class="success-icon"><i class="bi bi-check-circle" style="font-size: 3rem; color: var(--color-green);"></i></div>
          <h3 style="margin-top: 1rem;">Suggestion Submitted Successfully!</h3>
          <p style="margin-bottom: 2rem;">Thank you for sharing your idea. Our team will review it soon.</p>

          <div class="anonymous-notification" id="step4AnonymousAlert" style="display: none; flex-direction: row; align-items: center; gap: 1rem; margin: 1.5rem 0; padding: 1rem; background: var(--color-blue-light); border: 1px solid var(--color-blue); border-radius: 0.5rem;">
            <i class="bi bi-incognito" style="font-size: 1.5rem; color: var(--color-blue);"></i>
            <div>
              <p style="margin: 0; font-weight: 600;">Submitted Anonymously</p>
              <p style="margin: 0.25rem 0 0; font-size: 0.875rem;">Your name and contact information are hidden from administrators.</p>
            </div>
          </div>

          <div style="background: var(--color-gray-light); border: 2px solid var(--color-red); border-radius: 0.5rem; padding: 1.5rem; margin: 1.5rem 0; text-align: center;">
            <p style="margin: 0 0 0.5rem; font-size: 0.875rem;">Your Reference ID:</p>
            <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--color-red);" id="referenceId">-</p>
          </div>
          <p style="margin-top: 1rem; color: var(--color-gray);">Save this Reference ID to track your suggestion status from your dashboard.</p>
        </div>
      </div>

      <div class="form-actions">
        <button type="button" id="prevBtn" class="btn btn-back">Back</button>
        <button type="button" id="nextBtn" class="btn btn-next">Next</button>
        <button type="button" id="submitBtn" class="btn btn-success" style="display: none;"><i class="bi bi-check-circle"></i> Submit Suggestion</button>
        <button type="button" id="doneBtn" class="btn btn-success" style="display: none;">Done</button>
      </div>
    </form>
  </main>

  <?php include '../components/footer.php'; ?>

  <script src="../js/suggestion_form.js"></script>
  <script src="../js/navbar.js"></script>
  <script src="../js/global-theme.js"></script>
</body>
</html>