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
    <div class="login-box"> 
      <h2>Sign Up</h2>
      <form id="signupForm" method="POST" action="signup_handler.php">
        <div class="signup-row">
          <input type="text" name="first_name" placeholder="First Name" required>
          <input type="text" name="middle_initial" class="mi" placeholder="M.I" maxlength="1">
          <input type="text" name="last_name" placeholder="Last Name" required>
        </div>
        <input type="email" name="email" placeholder="GSuite account" required>
        <input type="text" name="student_id" placeholder="Student ID" required>
        <input type="tel" name="phone_number" placeholder="Contact Number" required>
        <input type="password" name="password" placeholder="Password (minimum 5 characters)" required minlength="5">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Sign Up</button>
        <p>Already have an account? <a href="login.php" id="backToLogin">Login here</a></p>
      </form>
    </div>
  </section>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
  <script src="login_signup.js"></script>
</body>
</html>
