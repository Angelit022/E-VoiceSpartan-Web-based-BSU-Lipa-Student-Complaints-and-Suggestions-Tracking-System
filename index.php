<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>E-Voice Spartan</title>
  <link rel="stylesheet" href="styles.css" />
</head>

<body>


  <!-- Faded hexagon shapes background -->
  <div class="floating-hexagons"></div>

  <!-- Red diagonal accent -->
  <div class="red-accent"></div>

  <div class="container">
    <header>
      <div class="logo-section">
        <!-- BSU Logo -->
        <img class="logo" src="images/Batangas_State_Logo.png" alt="Batangas State University Logo" />
        
        <div class="logo-divider"></div>
        
        <!-- E-Voice Spartan Logo -->
        <img class="logo" src="images/LOGO.png" alt="E-Voice Spartan Logo" />
      </div>

      <div class="text-group">
        <h1>BATANGAS STATE UNIVERSITY</h1>
        <h2>The National Engineering University</h2>
      </div>
    </header>

    <div class="main-content">
      <div class="left-content">
        <div class="title-wrapper">
          <h1 class="main-title">E -VOICE SPARTAN</h1>
        </div>

        <p class="subtitle" id="dynamic-subtitle">
          <!-- Typing effect text goes here -->
        </p>

        <div class="description">
          <p>Your voice matters. Make a difference in your campus experience. Share your thoughts, drive positive change, and help build a better university community. Join fellow Spartans in shaping the future—speak up today!</p>
        </div>

        <a href="signup_login/login.php" class="nav-button">Get Started</a>
      </div>

      <div class="right-content">
        <!-- Top row: 2 hexagons with images -->
        <div class="hexagon-wrapper">
          <img class="img1" src="images/complaintTEXT.jpg" alt="complaint" />
          <img class="img2" src="images/sumisigawSiAte.webp" alt="si ate nasigaw" />
        </div>
        <div class="hexagon-wrapper">
          <img class="img1" src="images/suggestioning.avif" alt="suggestion" />
          <img class="img2" src="images/complaintdigital.jpg" alt="complaint" />
        </div>
        
        <!-- Bottom row: 1 hexagon with image + 2 red hexagons -->
        <div class="hexagon-wrapper">
          <img class="img1" src="images/meetingDeAbanse.jpg" alt="meeting" />
          <img class="img2" src="images/complaint.webp" alt="complaint" />
        </div>
        <div class="hexagon-empty"></div>
        <div class="hexagon-empty"></div>
      </div>
    </div>
  </div>

  <footer>
    Leading Innovations, Transforming Lives, Building the Nation
  </footer>

  <script src="script.js"></script>
</body>

</html>