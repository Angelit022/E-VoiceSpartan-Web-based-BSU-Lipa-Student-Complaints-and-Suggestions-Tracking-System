<footer class="admin-footer">
    <div class="container-fluid px-0">
        <div class="footer-content">
            <div class="row align-items-center mx-0">
                <div class="col-12 col-md-4 text-center text-md-start mb-3 mb-md-0">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                        <div class="footer-logo me-2">
                            <img src="./../images/LOGO.png" alt="E-VoiceSpartan Logo" class="footer-logo-img">
                        </div>

                        <div>
                            <h6 class="mb-0 fw-bold text-white">E-VoiceSpartan</h6>
                            <small class="text-white-50">Admin Dashboard</small>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4 text-center mb-3 mb-md-0">
                    <small class="text-white-50">
                        © <?php echo date('Y'); ?> <strong class="text-white">Batangas State University</strong>
                        <br class="d-md-none">
                        <span class="d-none d-md-inline"> | </span>
                        All rights reserved.
                    </small>
                </div>

                <div class="col-12 col-md-4 text-center text-md-end">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-end gap-3">
                        <span class="badge bg-success bg-opacity-25 text-white border border-success border-opacity-50">
                            <i class="bi bi-circle-fill text-success me-1" style="font-size: 0.5rem;"></i>
                            System Online
                        </span>
                        <span class="badge bg-light bg-opacity-10 text-white-50">
                            v2.0 | Nov 2025
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.admin-footer {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 25%, #8f8a97ff 50%, #8f949bff 75%, #bc1717ff 100%);
    padding: 1.25rem 0;
    margin-top: auto;
    border-top: 3px solid #ffc107;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.25);
    width: 100%;
}

.footer-content {
    position: relative;
    padding: 0 1.5rem;
}

.footer-logo {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

.footer-logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 4px;
    mix-blend-mode: multiply;
}


.admin-footer .badge {
    font-weight: 500;
    padding: 0.4rem 0.75rem;
    font-size: 0.75rem;
}

.admin-footer h6 {
    font-size: 1rem;
    letter-spacing: 0.5px;
}

.admin-footer small {
    font-size: 0.8rem;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .admin-footer {
        padding: 1.5rem 0;
    }
    
    .footer-content {
        padding: 0 1rem;
    }
    
    .admin-footer .badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
    }
    
    .footer-logo {
        width: 35px;
        height: 35px;
    }
    
    .footer-logo i {
        font-size: 1rem;
    }
}

@media (max-width: 575.98px) {
    .admin-footer .d-flex.gap-3 {
        gap: 0.5rem !important;
    }
    
    .footer-content {
        padding: 0 0.5rem;
    }
}
</style>
