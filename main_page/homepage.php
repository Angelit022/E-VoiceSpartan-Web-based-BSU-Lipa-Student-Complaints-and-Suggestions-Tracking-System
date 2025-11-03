<?php
session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['authUser'])) {
  header("Location: ../signup_login/login.php");
  exit();
}

$student_id = $_SESSION['authUser'];

$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("SELECT first_name, middle_initial, last_name FROM student WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>E-VoiceSpartan</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <div class="navbar">
    <div class="brand" id="menu-btn">☰ E-VoiceSpartan</div>
    <div class="menu">
      <a href="#home" class="nav-link active">Home</a>
      <a href="#complaint" class="nav-link">File Complaint</a>
      <a href="#suggestion" class="nav-link">Submit Suggestion</a>
      <a href="javascript:void(0);" id="logoutBtn" class="logout">Logout</a>
    </div>
  </div>

  <div id="sidebar" class="sidebar">
    <a id="profile-toggle"><span class="arrow">▶</span>My Profile</a>
    <div id="profile-sub" class="sub-links">
      <a href="#">Personal Information</a>
      <a href="#">My Submissions</a>
    </div>

    <a id="settings-toggle"><span class="arrow">▶</span>Settings</a>
    <div id="settings-sub" class="sub-links">
      <a href="#">Account Setting</a>
      <a href="#">Anonymity Setting</a>
    </div>

    <hr>
    <a href="#">FAQs</a>
    <hr>
    <a href="#">About Us</a>
    <a href="#">Contact Us</a>
  </div>

  <div class="content" id="main-content">
    <section id="home" class="welcome-section">
      <div class="welcome-text">
      <h1>WELCOME</h1>
      <h2><?php echo htmlspecialchars($student['first_name']); ?></h2>
      <h3><?php echo htmlspecialchars(($student['middle_initial'] ? $student['middle_initial'] . '. ' : '') . $student['last_name']); ?></h3>

      </div>
      <div class="welcome-desc">
        This is the E-Voice Spartan platform where you can voice out your complaints and suggestions for a better campus experience.
      </div>
    </section>

    <section id="mission-vision" class="mission-vision">
      <div class="mv-box">
        <h3>Mission</h3>
        <p>Our mission is to create an open and secure platform where BSU Lipa students can freely express their concerns, complaints, and suggestions.</p>
        <p>Through this system, we aim to strengthen communication between students and the administration, promote transparency, and ensure that every voice is heard and valued.</p>
      </div>

      <div class="mv-box">
        <h3>Vision</h3>
        <p>We envision a university community where every student's voice contributes to positive change. Through this Complaints and Suggestions Tracking System, BSU Lipa strives to become a model of transparency, accountability, and student empowerment — a place where feedback drives growth, innovation, and continuous improvement for the benefit of all.</p>
      </div>
    </section>

    <footer class="footer">
      Leading Innovations, Transforming Lives, Building the Nation
    </footer>
  </div>

  <script src="script.js"></script>

  <script>
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Logout',
        text: 'Are you sure you want to logout?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Logout'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'logout.php';
        }
      });
    });
  </script>
</body>
</html>
