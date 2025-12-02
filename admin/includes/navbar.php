<?php
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-danger fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="?page=dashboard">
            <img src="./../images/LOGO.png" alt="Logo" style="height: 35px; width: auto;" class="me-2">
            <span class="d-none d-sm-inline">E-VoiceSpartan</span>
            <span class="d-inline d-sm-none">EVS</span>
        </a>
        
        <div class="d-none d-lg-flex flex-grow-1 justify-content-center">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>" href="?page=dashboard">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'responses' ? 'active' : ''; ?>" href="?page=responses">
                        <i class="bi bi-chat-dots me-1"></i> Responses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'feedback' ? 'active' : ''; ?>" href="?page=feedback">
                        <i class="bi bi-star me-1"></i> Feedback
                    </a>
                </li>
                <?php if (isSuperAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'activity_log' ? 'active' : ''; ?>" href="?page=activity_log">
                        <i class="bi bi-activity me-1"></i> Activity Logs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'profile' ? 'active' : ''; ?>" href="?page=profile">
                        <i class="bi bi-people me-1"></i> Admin Management
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'settings' ? 'active' : ''; ?>" href="?page=settings">
                       <i class="bi bi-person-circle me-2"></i> Profile
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="d-none d-lg-flex justify-content-end align-items-center">
            <a href="#" 
               id="logoutBtnDesktop"
               class="btn btn-outline-light btn-sm d-flex align-items-center">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </div>
        
        <button class="navbar-toggler border-0 d-lg-none" 
                type="button" 
                data-bs-toggle="offcanvas" 
                data-bs-target="#mobileMenu" 
                aria-controls="mobileMenu"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<div class="offcanvas offcanvas-end bg-dark text-white" 
     tabindex="-1" 
     id="mobileMenu" 
     aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header bg-danger">
        <h5 class="offcanvas-title fw-bold" id="mobileMenuLabel">
            <i class="bi bi-shield-check me-2"></i> Menu
        </h5>
        <button type="button" 
                class="btn-close btn-close-white" 
                data-bs-dismiss="offcanvas" 
                aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="p-3 bg-secondary border-bottom">
            <div class="d-flex align-items-center">
                <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 50px; height: 50px;">
                    <i class="bi bi-person-circle fs-3"></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($admin_name); ?></h6>
                    <small class="text-light"><?php echo htmlspecialchars($admin_email); ?></small>
                </div>
            </div>
        </div>
        
        <ul class="navbar-nav flex-column p-3">
            <li class="nav-item mb-2">
                <a class="nav-link text-white <?php echo $current_page === 'dashboard' ? 'active bg-danger rounded' : ''; ?>" 
                   href="index.php?page=dashboard">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link text-white <?php echo $current_page === 'responses' ? 'active bg-danger rounded' : ''; ?>" 
                   href="index.php?page=responses">
                    <i class="bi bi-chat-dots me-2"></i> Responses
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link text-white <?php echo $current_page === 'feedback' ? 'active bg-danger rounded' : ''; ?>" 
                   href="index.php?page=feedback">
                    <i class="bi bi-star me-2"></i> Feedback
                </a>
            </li>
            <?php if (isSuperAdmin()): ?>
            <li class="nav-item mb-2">
                <a class="nav-link text-white <?php echo $current_page === 'activity_log' ? 'active bg-danger rounded' : ''; ?>" 
                   href="index.php?page=activity_log">
                    <i class="bi bi-activity me-2"></i> Activity Logs
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link text-white <?php echo $current_page === 'profile' ? 'active bg-danger rounded' : ''; ?>" 
                   href="index.php?page=profile">
                    <i class="bi bi-people me-2"></i> Admin Management
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item mb-2">
                <a class="nav-link text-white <?php echo $current_page === 'settings' ? 'active bg-danger rounded' : ''; ?>" 
                   href="index.php?page=settings">
                    <i class="bi bi-person-circle me-2"></i> Profile
                </a>
            </li>
            
            <li class="nav-item mt-4 pt-3 border-top border-secondary">
                <a class="nav-link text-danger fw-bold" 
                   href="#"
                   id="logoutBtnMobile">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
.navbar {
    padding: 0.75rem 1rem;
    background: linear-gradient(to right, #ffffff 0%, #ff4d4d 10%, #dc3545 100%);
    transition: background 0.3s ease;
}

.navbar-brand {
    font-size: 1.25rem;
}

.navbar-brand img {
    mix-blend-mode: multiply;
    background-color: transparent;
}

.navbar-nav .nav-link {
    padding: 0.5rem 1rem;
    margin: 0 0.25rem;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
}

.navbar-nav .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.navbar-nav .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
    font-weight: 600;
}

.offcanvas {
    width: 280px !important;
}

.offcanvas-body .nav-link {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.offcanvas-body .nav-link:hover {
    background-color: rgba(220, 53, 69, 0.1);
}

.offcanvas-body .nav-link.active {
    background-color: #dc3545 !important;
    font-weight: 600;
}

@media (max-width: 991.98px) {
    .navbar .d-none.d-lg-flex {
        display: none !important;
    }
    
    .navbar-brand {
        font-size: 1rem;
    }
    
    .navbar-brand img {
        height: 30px !important;
    }
    
    .offcanvas {
        width: 260px !important;
    }
    #mobileMenu {
        background: linear-gradient(to bottom, #e6e6e6, #cccccc, #b3b3b3) !important;
        color: #ffffffff !important;
    }

    #mobileMenu .nav-link {
        color: #a70000ff !important;
    }

    #mobileMenu .nav-link.active {
        background-color: rgba(247, 2, 2, 0.4) !important;
        color: #fefefeff !important;
    }
}

@media (max-width: 575.98px) {
    .navbar {
        padding: 0.5rem 0.75rem;
    }
    
    .navbar-brand img {
        height: 28px !important;
    }
}

@media (min-width: 992px) {
    .navbar .d-none.d-lg-flex {
        display: flex !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const mobileMenuElement = document.getElementById('mobileMenu');
    
    if (mobileMenuElement) {
        const navLinks = mobileMenuElement.querySelectorAll('.nav-link:not([href*="logout"])');
        const mobileMenu = bootstrap.Offcanvas.getInstance(mobileMenuElement) || new bootstrap.Offcanvas(mobileMenuElement);
        
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.hide();
            });
        });
    }
    
    function handleLogout(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to logout?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#96a5b4ff',
            confirmButtonText: 'Logout',
            cancelButtonText: 'Cancel',
            allowOutsideClick: true,
            allowEscapeKey: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = './logout.php?confirmed=true';
            }
        });
    }
    
    const logoutBtnDesktop = document.getElementById('logoutBtnDesktop');
    const logoutBtnMobile = document.getElementById('logoutBtnMobile');
    
    if (logoutBtnDesktop) {
        logoutBtnDesktop.addEventListener('click', handleLogout);
    }
    
    if (logoutBtnMobile) {
        logoutBtnMobile.addEventListener('click', handleLogout);
    }
});
</script>