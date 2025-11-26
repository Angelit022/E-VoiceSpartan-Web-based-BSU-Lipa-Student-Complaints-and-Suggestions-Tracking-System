<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: ../signup_login/login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-VoiceSpartan - Your Voice, Our Priority</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="css/global-theme.css">
  <link rel="stylesheet" href="css/components.css">
  <link rel="stylesheet" href="css/homepage.css">
  <link rel="stylesheet" href="css/navbar.css">
  
</head>
<body>
  <?php include 'components/navbar.php'; ?>
  <main>
    <section class="hero-section">
      <div class="hero-background"></div>
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1 class="hero-title" style="color: red; font-size: 8rem; font-weight: 1000;">Your Voice, Our Priority</h1>
        <p class="hero-description">
          E-VoiceSpartan is your platform to file complaints, share suggestions, and drive meaningful change in our campus.
        </p>
        <div class="hero-buttons">
          <a href="complaint/complaint.php" class="btn btn-danger">File a Complaint</a>
          <a href="suggestion/suggestion.php" class="btn btn-danger">Share a Suggestion</a>
        </div>
      </div>
    </section>

    <section class="how-it-works-section">
      <div class="container">
        <h2 class="section-title">How It Works</h2>
        <div class="row g-4">
          <div class="col-lg-3 col-md-6">
            <div class="feature-card">
              <div class="feature-icon"><i class="bi bi-pencil-square"></i></div>
              <h3>File Complaint</h3>
              <p>Report issues with a simple 5-step process.</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="feature-card">
              <div class="feature-icon"><i class="bi bi-lightbulb"></i></div>
              <h3>Share Ideas</h3>
              <p>Suggest improvements for your community.</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="feature-card">
              <div class="feature-icon"><i class="bi bi-bar-chart-line"></i></div>
              <h3>Track Progress</h3>
              <p>Monitor the status of your submissions.</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="feature-card">
              <div class="feature-icon"><i class="bi bi-shield-lock"></i></div>
              <h3>Secure & Private</h3>
              <p>Your data is protected and confidential.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="why-choose-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <h2>Why Choose E-VoiceSpartan?</h2>
            <div class="why-choose-list">
              <div class="why-choose-item">
                <span class="why-choose-arrow"><i class="bi bi-chevron-right"></i></span>
                <div>
                  <h4>Simple & Intuitive</h4>
                  <p>Our user-friendly interface makes filing complaints and suggestions effortless.</p>
                </div>
              </div>
              <div class="why-choose-item">
                <span class="why-choose-arrow"><i class="bi bi-chevron-right"></i></span>
                <div>
                  <h4>Real-Time Tracking</h4>
                  <p>Stay updated on your submission status with instant notifications.</p>
                </div>
              </div>
              <div class="why-choose-item">
                <span class="why-choose-arrow"><i class="bi bi-chevron-right"></i></span>
                <div>
                  <h4>Community Driven</h4>
                  <p>Join thousands of voices working together for positive change.</p>
                </div>
              </div>
              <div class="why-choose-item">
                <span class="why-choose-arrow"><i class="bi bi-chevron-right"></i></span>
                <div>
                  <h4>Transparent Process</h4>
                  <p>Complete visibility into how your feedback is handled and resolved.</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="placeholder-image">
              <img src="../images/tower.jpg" alt="BSU" style="height: 700px; width: 700px;">
            </div>
          </div>
        </div>
      </div>
    </section>

<section id="mission-vision" class="mission-vision">
  <div class="container text-center">
    <h2 class="section-title" style="color: #d51c1c;">Our Mission & Vision</h2>
    <p class="section-subtitle">Guiding the purpose and direction of E-VoiceSpartan</p>

    <div class="row justify-content-center align-items-stretch g-4">
      <div class="col-md-6">
        <div class="mv-box mission-box">
          <div class="mv-icon">
            <i class="bi bi-bullseye"></i>
          </div>
          <h3>Mission</h3>
          <p>Our mission is to create an open and secure platform where BSU Lipa students can freely express their concerns, complaints, and suggestions.</p>
          <p>Through this system, we aim to strengthen communication between students and the administration, promote transparency, and ensure that every voice is heard and valued.</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="mv-box vision-box">
          <div class="mv-icon">
            <i class="bi bi-eye"></i>
          </div>
          <h3>Vision</h3>
          <p>We envision a university community where every student's voice contributes to positive change. Through this Complaints and Suggestions Tracking System, BSU Lipa strives to become a model of transparency, accountability, and student empowerment — a place where feedback drives growth, innovation, and continuous improvement for the benefit of all.</p>
        </div>
      </div>
    </div>
  </div>
</section>

    <section class="cta-section">
      <div class="container">
        <h2>Ready to Make a Difference?</h2>
        <p>Join thousands of voices making positive changes. Start by filing your first complaint or sharing your suggestion today.</p>
        <div class="cta-buttons">
          <a href="complaint/complaint.php" class="btn btn-primary">Get Started Now</a>
          <a href="#" class="btn btn-outline-dark">Learn More</a>
        </div>
      </div>
    </section>
  </main>

  <?php include 'components/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/navigation.js"></script>
  <script src="js/global-theme.js"></script>
  <script src="js/navbar.js"></script>
</body>
</html>