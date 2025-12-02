<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>E-VoiceSpartan | Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@800;900&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="otp/otp_components.css">
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
  <section class="section" id="login-section">
    <div class="login-box">
      <h2>Login</h2>
      <form id="loginForm" method="POST" action="login_handler.php">
        <input type="text" name="student_id" placeholder="Student ID" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <p>Don't have an account? <a href="signup.php" id="showSignup">Sign up here</a></p>
      </form>
    </div>
  </section>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
  <script src="login.js"></script>
</body>
</html>
