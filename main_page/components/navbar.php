<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>

<nav class="navbar d-flex align-items-center justify-content-between px-4 py-2">
  <!-- Brand -->
  <div class="navbar-brand d-flex align-items-center gap-2">
    <img src="/E-VoiceSpartan Web-based BSU Lipa Student Complaints and Suggestions Tracking System/images/LOGO.png" 
         alt="Logo" class="brand-logo">
    <span class="logo-text fw-semibold" style="color:#e63946">E-VoiceSpartan</span>
  </div>

  <!-- Centered Navigation Links -->
  <div class="navbar-menu d-flex justify-content-center align-items-center gap-4">
    <a href="/E-VoiceSpartan%20Web-based%20BSU%20Lipa%20Student%20Complaints%20and%20Suggestions%20Tracking%20System/main_page/homepage.php" 
       class="nav-link <?php echo ($current_page == 'homepage.php') ? 'active' : ''; ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
        <polyline points="9 22 9 12 15 12 15 22"></polyline>
      </svg>
      Home
    </a>

    <a href="/E-VoiceSpartan%20Web-based%20BSU%20Lipa%20Student%20Complaints%20and%20Suggestions%20Tracking%20System/main_page/complaint/complaint.php"
       class="nav-link <?php echo ($current_page == 'complaint.php') ? 'active' : ''; ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
      </svg>
      Complaint
    </a>

    <a href="/E-VoiceSpartan%20Web-based%20BSU%20Lipa%20Student%20Complaints%20and%20Suggestions%20Tracking%20System/main_page/suggestion/suggestion.php"
       class="nav-link <?php echo ($current_page == 'suggestion.php') ? 'active' : ''; ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"></path>
        <path d="M12 7v5h5"></path>
      </svg>
      Suggestion
    </a>
  </div>

  <!-- Right-side Icons -->
  <div class="navbar-icons d-flex align-items-center gap-3">
    <button class="icon-btn notification-btn position-relative" aria-label="Notifications">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
      </svg>
      <span class="notification-dot position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
    </button>

    <button class="icon-btn profile-btn" aria-label="User Profile">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>
    </button>

    <a href="/E-VoiceSpartan%20Web-based%20BSU%20Lipa%20Student%20Complaints%20and%20Suggestions%20Tracking%20System/main_page/logout.php" class="logout-btn" title="Logout">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
        <polyline points="16 17 21 12 16 7"></polyline>
        <line x1="21" y1="12" x2="9" y2="12"></line>
      </svg>
      Logout
    </a>
  </div>
</nav>

<!-- Include Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>
/* Keeps your existing style consistent */
.navbar {
  background-color: #fff;
  border-bottom: 1px solid #ddd;
}   
.brand-logo {
  width: 40px;
  height: 40px;
  object-fit: contain;
  border-radius: 8px;
}

/* Base style for links */
.nav-link {
  color: #333;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  padding: 5px 10px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

/* Hover and active states */
.nav-link:hover {
  color: #ff2032ff;
  background-color: rgba(230, 57, 70, 0.1);
}

.nav-link.active {
  background-color: #e92639c5;
  color: #fff;
  font-weight: 600;
  box-shadow: 0 2px 6px rgba(245, 67, 82, 0.3);
}



/* Icon and logout button styles */
.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #333;
  transition: color 0.3s ease, transform 0.2s ease;
}

.icon-btn:hover {
  color: #e63946;
  transform: scale(1.1);
}

.logout-btn {
  color: #333;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 10px;
}

.logout-btn:hover {
  color: #e4b6bbff;
}

/* Logo icon styling */
.logo-icon {
  background-color: #e63946;
  color: white;
  padding: 6px 10px;
  border-radius: 8px;
  font-weight: bold;
}
</style>

