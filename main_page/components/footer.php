<footer class="footer mt-5">
  <div class="container py-5">
    <div class="row g-4">
      <!-- About Section -->
      <div class="col-lg-4 col-md-6">
        <div class="footer-brand mb-3">
          <img src="/E-VoiceSpartan Web-based BSU Lipa Student Complaints and Suggestions Tracking System/images/LOGO.png" 
               alt="Logo" class="footer-logo mb-2">
          <h5 class="fw-bold text-danger">E-VoiceSpartan</h5>
        </div>
        <p class="text-muted small">
          Your voice matters. A comprehensive tracking system for student complaints and suggestions at BSU Lipa.
        </p>
      </div>

      <!-- Quick Links -->
      <div class="col-lg-2 col-md-6 col-6">
        <h6 class="fw-bold mb-3">Quick Links</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="<?php echo get_nav_path('homepage'); ?>">Home</a></li>
          <li><a href="<?php echo get_nav_path('complaint'); ?>">Complaint</a></li>
          <li><a href="<?php echo get_nav_path('suggestion'); ?>">Suggestion</a></li>
          <li><a href="<?php echo get_nav_path('notification'); ?>">Notifications</a></li>
        </ul>
      </div>

      <!-- About -->
      <div class="col-lg-3 col-md-6 col-6">
        <h6 class="fw-bold mb-3">About</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="#" data-bs-toggle="modal" data-bs-target="#aboutModal">About Us</a></li>
          <li><a href="<?php echo get_nav_path('profile'); ?>">Profile</a></li>
          <li><a href="<?php echo get_nav_path('settings'); ?>">Settings</a></li>
        </ul>
      </div>

      <!-- Legal -->
      <div class="col-lg-3 col-md-6">
        <h6 class="fw-bold mb-3">Legal</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a></li>
          <li><a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a></li>
          <li><a href="#" data-bs-toggle="modal" data-bs-target="#disclaimerModal">Disclaimer</a></li>
        </ul>
      </div>
    </div>

    <hr class="my-4 border-secondary">

    <div class="row align-items-center">
      <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
        <p class="mb-0 small text-muted">
          &copy; <?php echo date('Y'); ?> E-VoiceSpartan. All rights reserved.
        </p>
      </div>
      <div class="col-md-6 text-center text-md-end">
        <p class="mb-0 small text-muted">
          Batangas State University - Lipa Campus
        </p>
      </div>
    </div>
  </div>
</footer>

<!-- About Us Modal -->
<div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-0 bg-danger text-white">
        <h5 class="modal-title fw-bold" id="aboutModalLabel">
          <i class="bi bi-people-fill me-2"></i>About Us
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="text-center mb-4">
          <h6 class="text-muted">Meet the Team Behind E-VoiceSpartan</h6>
          <p class="small text-muted">Dedicated to improving student experience at BSU Lipa</p>
        </div>

        <div class="row g-4">
          <!-- Team Member 1 -->
          <div class="col-lg-4 col-md-6">
            <div class="team-card text-center h-100">
              <div class="team-img-wrapper mb-3">
                <img src="../../images/vata.jpg"; 
                     alt="Team Member" class="team-img rounded-circle">
              </div>
              <h6 class="fw-bold mb-1">Angelito Gonzales</h6>
              <p class="text-danger small fw-semibold mb-2">UI/UX Designer & Backend Dev</p>
              <p class="small text-muted">Leading the development design and logic implementation of the E-VoiceSpartan system.</p>
            </div>
          </div>

          <!-- Team Member 2 -->
          <div class="col-lg-4 col-md-6">
            <div class="team-card text-center h-100">
              <div class="team-img-wrapper mb-3">
                <img src="/E-VoiceSpartan Web-based BSU Lipa Student Complaints and Suggestions Tracking System/images/team-member-2.jpg" 
                     alt="Team Member" class="team-img rounded-circle">
              </div>
              <h6 class="fw-bold mb-1">Angelica Ramirez</h6>
              <p class="text-danger small fw-semibold mb-2">Data Analyst & Backend Dev</p>
              <p class="small text-muted">Handle documentation and building robust systems to ensure efficient complaint and suggestion tracking.</p>
            </div>
          </div>

          <!-- Team Member 3 -->
          <div class="col-lg-4 col-md-6">
            <div class="team-card text-center h-100">
              <div class="team-img-wrapper mb-3">
                <img src="/E-VoiceSpartan Web-based BSU Lipa Student Complaints and Suggestions Tracking System/images/team-member-3.jpg" 
                     alt="Team Member" class="team-img rounded-circle">
              </div>
              <h6 class="fw-bold mb-1">Klyza Mae Medrazo</h6>
              <p class="text-danger small fw-semibold mb-2">UI/UX Designer</p>
              <p class="small text-muted">Crafting intuitive and user-friendly interfaces for better student and admin experience.</p>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-4 border-top">
          <h6 class="fw-bold mb-3">Our Mission</h6>
          <p class="text-muted small">
            E-VoiceSpartan aims to bridge the gap between students and administration at BSU Lipa. 
            We provide a transparent, efficient platform where every student voice is heard, tracked, 
            and addressed. Our commitment is to foster a responsive academic environment that prioritizes 
            student welfare and continuous improvement.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Privacy Policy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-0 bg-danger text-white">
        <h5 class="modal-title fw-bold" id="privacyModalLabel">
          <i class="bi bi-shield-lock-fill me-2"></i>Privacy Policy
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-muted small mb-4">
          <strong>Effective Date:</strong> <?php echo date('F d, Y'); ?>
        </p>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">1. Information We Collect</h6>
          <p class="text-muted small">
            We collect information you provide directly to us, including but not limited to:
          </p>
          <ul class="text-muted small">
            <li>Personal identification information (name, email address, student ID)</li>
            <li>Complaint and suggestion submissions</li>
            <li>Communication records and correspondence</li>
            <li>Usage data and system interactions</li>
          </ul>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">2. How We Use Your Information</h6>
          <p class="text-muted small">
            Your information is used to:
          </p>
          <ul class="text-muted small">
            <li>Process and track complaints and suggestions</li>
            <li>Communicate updates regarding your submissions</li>
            <li>Improve our services and user experience</li>
            <li>Ensure system security and prevent unauthorized access</li>
            <li>Comply with legal obligations</li>
          </ul>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">3. Data Protection & Security</h6>
          <p class="text-muted small">
            We implement appropriate technical and organizational measures to protect your personal 
            information against unauthorized access, alteration, disclosure, or destruction. All data 
            transmissions are encrypted using industry-standard protocols.
          </p>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">4. Information Sharing</h6>
          <p class="text-muted small">
            We do not sell, trade, or rent your personal information. Information may be shared with:
          </p>
          <ul class="text-muted small">
            <li>University administration for complaint resolution</li>
            <li>Relevant departments based on submission categories</li>
            <li>Legal authorities when required by law</li>
          </ul>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">5. Your Rights</h6>
          <p class="text-muted small">
            You have the right to:
          </p>
          <ul class="text-muted small">
            <li>Access your personal information</li>
            <li>Request correction of inaccurate data</li>
            <li>Request deletion of your data (subject to legal requirements)</li>
            <li>Opt-out of non-essential communications</li>
          </ul>
        </div>

        <div class="legal-section">
          <h6 class="fw-bold text-danger mb-3">6. Contact Us</h6>
          <p class="text-muted small">
            For privacy-related inquiries, please contact us at:<br>
            <strong>Email:</strong> privacy@evoicespartan.bsu.edu.ph<br>
            <strong>Office:</strong> BSU Lipa - Office of Student Affairs
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Terms of Service Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-0 bg-danger text-white">
        <h5 class="modal-title fw-bold" id="termsModalLabel">
          <i class="bi bi-file-text-fill me-2"></i>Terms of Service
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-muted small mb-4">
          <strong>Last Updated:</strong> <?php echo date('F d, Y'); ?>
        </p>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">1. Acceptance of Terms</h6>
          <p class="text-muted small">
            By accessing and using E-VoiceSpartan, you accept and agree to be bound by these Terms of Service. 
            If you do not agree to these terms, please do not use this platform.
          </p>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">2. Eligibility</h6>
          <p class="text-muted small">
            This service is exclusively available to currently enrolled students of Batangas State University - 
            Lipa Campus. You must use your official BSU email address to access the system.
          </p>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">3. User Responsibilities</h6>
          <p class="text-muted small">You agree to:</p>
          <ul class="text-muted small">
            <li>Provide accurate and truthful information in all submissions</li>
            <li>Use the platform solely for legitimate complaints and suggestions</li>
            <li>Maintain the confidentiality of your account credentials</li>
            <li>Refrain from submitting false, misleading, or malicious content</li>
            <li>Respect the rights and dignity of others in all communications</li>
            <li>Not engage in harassment, discrimination, or abusive behavior</li>
          </ul>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">4. Prohibited Activities</h6>
          <p class="text-muted small">The following activities are strictly prohibited:</p>
          <ul class="text-muted small">
            <li>Submitting spam, duplicate, or frivolous complaints</li>
            <li>Attempting to compromise system security or integrity</li>
            <li>Impersonating other users or university personnel</li>
            <li>Using the platform for commercial or promotional purposes</li>
            <li>Sharing access credentials with unauthorized individuals</li>
          </ul>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">5. Content Ownership</h6>
          <p class="text-muted small">
            You retain ownership of the content you submit. However, by submitting content, you grant 
            BSU Lipa a license to use, store, and process your submissions for the purpose of addressing 
            complaints and improving university services.
          </p>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">6. Account Termination</h6>
          <p class="text-muted small">
            BSU Lipa reserves the right to suspend or terminate accounts that violate these terms, 
            engage in prohibited activities, or misuse the platform. Accounts will be automatically 
            deactivated upon graduation or withdrawal from the university.
          </p>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">7. Modifications to Service</h6>
          <p class="text-muted small">
            We reserve the right to modify, suspend, or discontinue any aspect of the service at any 
            time without prior notice. We will make reasonable efforts to notify users of significant changes.
          </p>
        </div>

        <div class="legal-section">
          <h6 class="fw-bold text-danger mb-3">8. Governing Law</h6>
          <p class="text-muted small">
            These terms are governed by the laws of the Republic of the Philippines and university policies. 
            Any disputes shall be resolved through the university's internal grievance procedures.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Disclaimer Modal -->
<div class="modal fade" id="disclaimerModal" tabindex="-1" aria-labelledby="disclaimerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-0 bg-danger text-white">
        <h5 class="modal-title fw-bold" id="disclaimerModalLabel">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>Disclaimer
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="alert alert-warning border-0 mb-4">
          <i class="bi bi-info-circle-fill me-2"></i>
          <strong>Important Notice:</strong> Please read this disclaimer carefully before using E-VoiceSpartan.
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">1. General Information</h6>
          <p class="text-muted small">
            E-VoiceSpartan is provided as a service to facilitate communication between students and 
            BSU Lipa administration. While we strive to process all submissions promptly, we cannot 
            guarantee specific response times or outcomes for individual complaints or suggestions.
          </p>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">2. No Legal Advice</h6>
          <p class="text-muted small">
            The information and responses provided through this platform do not constitute legal advice. 
            For legal matters, students should consult with appropriate legal counsel or the university's 
            legal office.
          </p>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">3. Limitation of Liability</h6>
          <p class="text-muted small">
            BSU Lipa and the E-VoiceSpartan team shall not be liable for:
          </p>
          <ul class="text-muted small">
            <li>Any delays in processing or responding to submissions</li>
            <li>Technical issues, system downtime, or data loss</li>
            <li>Actions or decisions made by third parties based on submitted information</li>
            <li>Indirect, consequential, or incidental damages arising from platform use</li>
          </ul>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">4. System Availability</h6>
          <p class="text-muted small">
            While we endeavor to maintain continuous service availability, E-VoiceSpartan may experience 
            downtime for maintenance, updates, or unforeseen technical issues. We are not responsible for 
            any inconvenience caused by service interruptions.
          </p>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">5. Emergency Situations</h6>
          <p class="text-muted small">
            <strong>This platform is NOT for emergencies.</strong> In case of emergencies requiring immediate 
            attention (safety threats, medical emergencies, severe harassment), contact:
          </p>
          <ul class="text-muted small">
            <li>BSU Lipa Security Office: (043) 723-0706</li>
            <li>Office of Student Affairs: (043) 723-0871 local 104</li>
            <li>Emergency Hotline: 911</li>
          </ul>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">6. Content Accuracy</h6>
          <p class="text-muted small">
            Users are responsible for the accuracy of their submissions. BSU Lipa is not liable for 
            decisions made based on inaccurate or incomplete information provided by users.
          </p>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">7. Third-Party Links</h6>
          <p class="text-muted small">
            Our platform may contain links to external websites. We are not responsible for the content, 
            privacy practices, or availability of third-party sites.
          </p>
        </div>

        <div class="legal-section mb-4">
          <h6 class="fw-bold text-danger mb-3">8. Changes to Disclaimer</h6>
          <p class="text-muted small">
            This disclaimer may be updated periodically. Continued use of the platform after changes 
            constitutes acceptance of the updated disclaimer.
          </p>
        </div>

        <div class="alert alert-info border-0 mb-0">
          <i class="bi bi-envelope-fill me-2"></i>
          <strong>Questions?</strong> Contact us at support@evoicespartan.bsu.edu.ph
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.footer {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  color: #fff;
  margin-top: auto;
}

.footer-logo {
  width: 45px;
  height: 45px;
  object-fit: contain;
}

.footer-links {
  padding-left: 0;
}

.footer-links li {
  margin-bottom: 0.5rem;
}

.footer-links a {
  color: #adb5bd;
  text-decoration: none;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.footer-links a:hover {
  color: #e63946;
  padding-left: 5px;
}

/* Modal Enhancements */
.modal.show {
  backdrop-filter: blur(8px);
  background-color: rgba(0, 0, 0, 0.6);
}

.modal-content {
  border-radius: 16px;
  overflow: hidden;
}

.modal-header {
  padding: 1.5rem;
}

.modal-body {
  max-height: 70vh;
}

/* Team Card Styles */
.team-card {
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.team-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(230, 57, 70, 0.15);
}

.team-img-wrapper {
  position: relative;
  width: 150px;
  height: 150px;
  margin: 0 auto;
}

.team-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border: 4px solid #e63946;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Legal Section Styles */
.legal-section {
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  border-left: 4px solid #e63946;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .footer .col-6 {
    margin-bottom: 1.5rem;
  }
  
  .team-img-wrapper {
    width: 120px;
    height: 120px;
  }
  
  .modal-body {
    max-height: 60vh;
  }
}

@media (max-width: 576px) {
  .footer-logo {
    width: 35px;
    height: 35px;
  }
  
  .modal-dialog {
    margin: 0.5rem;
  }
  
  .modal-body {
    padding: 1.5rem !important;
  }
  
  .team-card {
    padding: 1rem;
  }
}

/* Smooth Scrolling for Modal */
.modal-dialog-scrollable .modal-body {
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #e63946 #f1f1f1;
}

.modal-dialog-scrollable .modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-dialog-scrollable .modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb {
  background: #e63946;
  border-radius: 10px;
}

.modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb:hover {
  background: #d32f3e;
}
</style>