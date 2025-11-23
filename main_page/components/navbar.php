<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Get student information from database
$studentName = "Student Name";
$studentEmail = "student@gsuite.bsu.edu.ph";

if (isset($_SESSION['user_id'])) {
    require_once(__DIR__ . '/../../db.php');
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT first_name, last_name, email FROM student WHERE student_id = ?");
    $stmt->bind_param("s", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $studentName = ucfirst($row['first_name']) . ' ' . ucfirst($row['last_name']);
        $studentEmail = $row['email'];
    }
    
    $stmt->close();
}

function get_nav_path($target_page) {
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    $is_main_page = $current_dir === 'main_page' || $current_dir === 'components' || $current_dir === 'css' || $current_dir === 'js' || $current_dir === 'classes';
    
    if ($is_main_page) {
        if ($target_page === 'homepage') return 'homepage.php';
        if ($target_page === 'complaint') return './complaint/complaint.php';
        if ($target_page === 'suggestion') return './suggestion/suggestion.php';
        if ($target_page === 'notification') return './notifications/notification.php';
        if ($target_page === 'profile') return './profile/profile.php';
        if ($target_page === 'settings') return './settings/settings.php';
        if ($target_page === 'logout') return './logout.php';
    } else {
        if ($target_page === 'homepage') return '../homepage.php';
        if ($target_page === 'complaint') return '../complaint/complaint.php';
        if ($target_page === 'suggestion') return '../suggestion/suggestion.php';
        if ($target_page === 'notification') return '../notifications/notification.php';
        if ($target_page === 'profile') return '../profile/profile.php';
        if ($target_page === 'settings') return '../settings/settings.php';
        if ($target_page === 'logout') return '../logout.php';
    }
    return '#';
}

function is_active($page_name) {
    $current_page = basename($_SERVER['PHP_SELF']);
    
    if ($page_name === 'homepage' && $current_page === 'homepage.php') return true;
    if ($page_name === 'complaint' && $current_page === 'complaint.php') return true;
    if ($page_name === 'suggestion' && $current_page === 'suggestion.php') return true;
    if ($page_name === 'notification' && $current_page === 'notification.php') return true;
    
    return false;
}
?>

<nav class="navbar navbar-expand-lg d-flex align-items-center justify-content-between px-4 py-2">
  <div class="navbar-brand d-flex align-items-center gap-2">
    <img src="/E-VoiceSpartan Web-based BSU Lipa Student Complaints and Suggestions Tracking System/images/LOGO.png" 
         alt="Logo" class="brand-logo">
    <span class="logo-text fw-semibold" style="color:#e63946">E-VoiceSpartan</span>
  </div>

  <button class="navbar-toggler border-0 d-lg-none" type="button" id="menuToggleBtn">
    <i class="bi bi-list fs-3" style="color:#e63946;"></i>
  </button>

  <div class="navbar-menu d-none d-md-flex justify-content-center align-items-center gap-4">
    <a href="<?php echo get_nav_path('homepage'); ?>" 
       class="nav-link <?php echo is_active('homepage') ? 'active' : ''; ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
        <polyline points="9 22 9 12 15 12 15 22"></polyline>
      </svg>
      Home
    </a>

    <a href="<?php echo get_nav_path('complaint'); ?>"
       class="nav-link <?php echo is_active('complaint') ? 'active' : ''; ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
      </svg>
      Complaint
    </a>

    <a href="<?php echo get_nav_path('suggestion'); ?>"
       class="nav-link <?php echo is_active('suggestion') ? 'active' : ''; ?>">
      <i class="bi bi-chat-dots"></i>
      Suggestion
    </a>
  </div>

  <div class="navbar-icons d-none d-lg-flex align-items-center gap-3 ms-lg-3">
    <a href="<?php echo get_nav_path('notification'); ?>" class="icon-btn notification-btn position-relative" aria-label="Notifications">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
      </svg>
      <span class="notification-dot position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
    </a>

    <button class="icon-btn dots-menu-btn" aria-label="Menu" id="dotsMenuBtn">
      <i class="bi bi-three-dots-vertical"></i>
    </button>
  </div>
</nav>

<div id="rightMenuBar" class="right-menu-bar">
  <div class="right-menu-header d-flex justify-content-between align-items-center">
    <h5 class="m-0 fw-bold text-danger">Menu</h5>
    <button class="close-btn-right"><i class="bi bi-x-lg"></i></button>
  </div>

  <div class="right-menu-body mt-10">
    <a href="<?php echo get_nav_path('profile'); ?>" class="right-menu-item">
      <i class="bi bi-person-circle"></i> Profile
    </a>
    <a href="<?php echo get_nav_path('settings'); ?>" class="right-menu-item">
      <i class="bi bi-gear"></i> Settings
    </a>
  </div>

  <div class="right-menu-footer mt-auto pt-3 border-top">
    <p class="fw-semibold mb-2"><?php echo htmlspecialchars($studentName); ?></p>
    <small class="text-muted"><?php echo htmlspecialchars($studentEmail); ?></small>
    <a href="#" class="logout-btn-right" id="logoutBtnRight">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</div>

<div id="rightOverlay"></div>

<div id="sidebarMenu" class="sidebar">
  <div class="sidebar-header d-flex justify-content-between align-items-center">
    <h5 class="m-0 fw-bold text-danger">MENU</h5>
    <button class="close-btn"><i class="bi bi-x-lg"></i></button>
  </div>

  <div class="sidebar-body mt-4">
    <a href="<?php echo get_nav_path('homepage'); ?>" class="sidebar-item <?php echo is_active('homepage') ? 'active' : ''; ?>">
      <i class="bi bi-house"></i> Home
    </a>
    <a href="<?php echo get_nav_path('complaint'); ?>" class="sidebar-item <?php echo is_active('complaint') ? 'active' : ''; ?>">
      <i class="bi bi-chat-left-dots"></i> Complaint
    </a>
    <a href="<?php echo get_nav_path('suggestion'); ?>" class="sidebar-item <?php echo is_active('suggestion') ? 'active' : ''; ?>">
      <i class="bi bi-chat-dots"></i> Suggestion
    </a>
    <hr class="my-3">

    <a href="<?php echo get_nav_path('notification'); ?>" class="sidebar-item">
      <i class="bi bi-bell"></i> Notifications
    </a>
    <a href="<?php echo get_nav_path('profile'); ?>" class="sidebar-item">
      <i class="bi bi-person-circle"></i> Profile
    </a>
    <a href="<?php echo get_nav_path('settings'); ?>" class="sidebar-item">
      <i class="bi bi-gear"></i> Settings
    </a>
  </div>

  <div class="sidebar-footer mt-auto pt-3 border-top">
    <p class="fw-semibold mb-2"><?php echo htmlspecialchars($studentName); ?></p>
    <small class="text-muted"><?php echo htmlspecialchars($studentEmail); ?></small>
    <a href="#" class="logout-btn-right" id="logoutBtnSidebar">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</div>

<div id="overlay"></div>

<script>
(function() {
  function initNavbar() {
    const sidebar = document.getElementById("sidebarMenu");
    const overlay = document.getElementById("overlay");
    const menuToggleBtn = document.getElementById("menuToggleBtn");
    const closeBtn = document.querySelector(".close-btn");
    const rightMenuBar = document.getElementById("rightMenuBar");
    const rightOverlay = document.getElementById("rightOverlay");
    const dotsMenuBtn = document.getElementById("dotsMenuBtn");
    const closeRightBtn = document.querySelector(".close-btn-right");
    const logoutBtnRight = document.getElementById("logoutBtnRight");
    const logoutBtnSidebar = document.getElementById("logoutBtnSidebar");

    if (menuToggleBtn && sidebar && overlay) {
      menuToggleBtn.addEventListener("click", function() {
        sidebar.classList.add("active");
        overlay.classList.add("active");
      });
    }

    if (closeBtn && sidebar && overlay) {
      closeBtn.addEventListener("click", function() {
        sidebar.classList.remove("active");
        overlay.classList.remove("active");
      });
    }

    if (overlay && sidebar) {
      overlay.addEventListener("click", function() {
        sidebar.classList.remove("active");
        overlay.classList.remove("active");
      });
    }

    document.querySelectorAll(".sidebar-item").forEach(function(item) {
      item.addEventListener("click", function() {
        if (sidebar && overlay) {
          sidebar.classList.remove("active");
          overlay.classList.remove("active");
        }
      });
    });

    if (dotsMenuBtn && rightMenuBar && rightOverlay) {
      dotsMenuBtn.addEventListener("click", function() {
        rightMenuBar.classList.add("active");
        rightOverlay.classList.add("active");
      });
    }

    if (closeRightBtn && rightMenuBar && rightOverlay) {
      closeRightBtn.addEventListener("click", function() {
        rightMenuBar.classList.remove("active");
        rightOverlay.classList.remove("active");
      });
    }

    if (rightOverlay && rightMenuBar) {
      rightOverlay.addEventListener("click", function() {
        rightMenuBar.classList.remove("active");
        rightOverlay.classList.remove("active");
      });
    }

    document.querySelectorAll(".right-menu-item").forEach(function(item) {
      item.addEventListener("click", function() {
        if (rightMenuBar && rightOverlay) {
          rightMenuBar.classList.remove("active");
          rightOverlay.classList.remove("active");
        }
      });
    });

    function handleLogout(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const currentPath = window.location.pathname;
      const isSubdirectory = currentPath.includes('/complaint/') || 
                             currentPath.includes('/suggestion/') || 
                             currentPath.includes('/notifications/') ||
                             currentPath.includes('/profile/') ||
                             currentPath.includes('/settings/');
      const logoutPath = isSubdirectory ? '../logout.php' : './logout.php';
      
      if (typeof Swal === 'undefined') {
        if (confirm("Do you want to logout?")) {
          window.location.href = logoutPath + '?confirmed=true';
        }
        return;
      }
      
      Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to logout?",
        icon: 'warning',
        showCancelButton: true,
        cancelButtonColor: '#8b9093ff',
        confirmButtonColor: '#d33',
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Logout'
      }).then(function(result) {
        if (result.isConfirmed) {
          window.location.href = logoutPath + '?confirmed=true';
        }
      });
    }

    if (logoutBtnRight) {
      logoutBtnRight.addEventListener("click", handleLogout);
    }

    if (logoutBtnSidebar) {
      logoutBtnSidebar.addEventListener("click", handleLogout);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNavbar);
  } else {
    initNavbar();
  }
})();
</script>