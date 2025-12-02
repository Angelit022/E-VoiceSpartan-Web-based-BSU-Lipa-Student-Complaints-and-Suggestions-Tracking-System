<?php
session_start();

require_once __DIR__ . '/classes/GoogleAuthService.php';

$googleAuth = new GoogleAuthService();
$authUrl = $googleAuth->getAuthUrl();

if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    unset($_SESSION['google_signup_data']);
    unset($_SESSION['signup_step']);
    unset($_SESSION['signup_error']);
    header('Location: signup.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>E-VoiceSpartan | Sign Up</title>
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@800;900&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="header">
    <img class="bg" src="../images/bg.jpg" alt="Lipa City background">
    <div class="slant"></div>
    <div class="text">
      <h1>E-VoiceSpartan</h1>
      <p class="lead">Your voice matters — share your thoughts and be heard!</p>
      <p class="small">Leading Innovations, Transforming Lives, Building the Nation</p>
    </div>
  </header>
  
  <section class="section" id="signup-section">
    <div class="login-box" id="signup-container">
      
      <?php
      if (isset($_SESSION['signup_error'])) {
          echo '<script>
              Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: "' . addslashes($_SESSION['signup_error']) . '"
              });
          </script>';
          unset($_SESSION['signup_error']);
      }
      ?>
      
      <?php if (!isset($_SESSION['google_signup_data'])): ?>
        <h2>Sign Up</h2>
        <div class="google-signup-wrapper">
          <p class="signup-instruction">Create your account using your BSU GSuite email address</p>
          <a href="<?= htmlspecialchars($authUrl) ?>" class="google-btn">
            <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
              <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
              <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
              <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
              <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
              
            </svg>
            Sign up with Google
          </a>
          <p class="signup-note">Only @g.batstate-u.edu.ph accounts are allowed</p>
          <p class="back-link">Already have an account? <a href="login.php">Login here</a></p>
        </div>
      <?php else: ?>
        
        <?php 
        $data = $_SESSION['google_signup_data'];
        $step = isset($_SESSION['signup_step']) ? $_SESSION['signup_step'] : 'mobile';
        ?>
        
        <?php if ($step === 'mobile'): ?>
          <h2>Complete Your Registration</h2>
          <p class="step-info">Step 1 of 2: Mobile Number Verification</p>
          <form id="mobileForm" autocomplete="off">
            <input type="hidden" name="step" value="mobile">
            <div class="form-group">
              <label for="phone_number">Mobile Number</label>
              <input 
                type="tel" 
                name="phone_number" 
                id="phone_number"
                placeholder="09XXXXXXXXX" 
                required 
                inputmode="numeric" 
                pattern="[0-9]*"
                maxlength="11"
                autocomplete="off">
              <div class="validation-message" id="mobile-validation"></div>
            </div>
            <button type="submit" id="mobile-submit" disabled>Continue</button>
            <p class="back-link"><a href="?reset=1">Use different account</a></p>
          </form>
        <?php elseif ($step === 'password'): ?>
          <h2>Set Your Password</h2>
          <p class="step-info">Step 2 of 2: Create a Secure Password</p>
          <form id="passwordForm" autocomplete="off">
            <input type="hidden" name="step" value="password">
            <div class="form-group">
              <label for="password">Password</label>
              <input 
                type="password" 
                name="password" 
                id="password"
                placeholder="Enter your password" 
                required
                autocomplete="new-password">
              <div class="validation-message" id="password-validation"></div>
            </div>
            <div class="password-requirements" id="password-requirements">
              <p class="req-title">Password must contain:</p>
              <ul>
                <li id="req-length" class="invalid">At least 8 characters</li>
                <li id="req-uppercase" class="invalid">One uppercase letter (A-Z)</li>
                <li id="req-lowercase" class="invalid">One lowercase letter (a-z)</li>
                <li id="req-number" class="invalid">One number (0-9)</li>
                <li id="req-special" class="invalid">One special character (@$!%*?&)</li>
              </ul>
            </div>
            <button type="submit" id="password-submit" disabled>Complete Sign up</button>
            <p class="back-link"><a href="?reset=1">Use different account</a></p>
          </form>
        <?php endif; ?>
        
      <?php endif; ?>
      
    </div>
  </section>
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="signup_flow.js"></script>
</body>
</html>