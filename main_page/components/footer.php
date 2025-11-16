<style>
.footer {
 background: linear-gradient(135deg, #4d4d4d 0%, #3a3a3a 100%);
  color: rgba(255, 255, 255, 0.9);
  padding: 4rem 0 2rem;
  margin-top: 5rem;
  position: relative;
  overflow: hidden;
}

.footer::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #c41e3a 0%, #8b1529 50%, #c41e3a 100%);
  box-shadow: 0 2px 10px rgba(196, 30, 58, 0.5);
}

.footer::after {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(196, 30, 58, 0.1) 0%, transparent 70%);
  border-radius: 50%;
  pointer-events: none;
}

.footer-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 3rem;
  position: relative;
  z-index: 1;
}

.footer-section h4 {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  color: #fff;
  position: relative;
  display: inline-block;
}

.footer-section h4::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 40px;
  height: 3px;
  background: linear-gradient(90deg, #c41e3a 0%, transparent 100%);
  border-radius: 2px;
}

.footer-logo {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.logo-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #c41e3a 0%, #8b1529 100%);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
  font-size: 1.5rem;
  color: white;
  box-shadow: 0 8px 20px rgba(196, 30, 58, 0.3);
  transition: all 0.3s ease;
}

.footer-logo:hover .logo-icon {
  transform: translateY(-4px) scale(1.05);
  box-shadow: 0 12px 30px rgba(196, 30, 58, 0.4);
}

.footer-logo span {
  font-size: 1.5rem;
  font-weight: 800;
  color: white;
  letter-spacing: -0.5px;
}

.footer-desc {
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.8;
  font-size: 0.95rem;
  margin: 0;
}

.footer-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-section ul li {
  margin-bottom: 1rem;
}

.footer-section ul li a {
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  position: relative;
  padding-left: 0;
}

.footer-section ul li a::before {
  content: '→';
  opacity: 0;
  transform: translateX(-10px);
  transition: all 0.3s ease;
  color: #c41e3a;
  font-weight: 700;
}

.footer-section ul li a:hover {
  color: #fff;
  padding-left: 1.5rem;
}

.footer-section ul li a:hover::before {
  opacity: 1;
  transform: translateX(0);
}

.footer-bottom {
  max-width: 1400px;
  margin: 3rem auto 0;
  padding: 2rem 2rem 0;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
  position: relative;
  z-index: 1;
}

.footer-bottom p {
  margin: 0;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9rem;
  font-weight: 500;
}

/* Responsive Design */
@media (max-width: 992px) {
  .footer-container {
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
  }
}

@media (max-width: 576px) {
  .footer {
    padding: 3rem 0 1.5rem;
  }

  .footer-container {
    grid-template-columns: 1fr;
    padding: 0 1.5rem;
    gap: 2rem;
  }

  .footer-section {
    text-align: center;
  }

  .footer-logo {
    justify-content: center;
  }

  .footer-section h4::after {
    left: 50%;
    transform: translateX(-50%);
  }

  .footer-section ul li a:hover {
    padding-left: 0;
  }

  .footer-section ul li a::before {
    content: none;
  }
}

/* Social Media Icons (Optional Addition) */
.footer-social {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.footer-social a {
  width: 45px;
  height: 45px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.7);
  font-size: 1.25rem;
  transition: all 0.3s ease;
  text-decoration: none;
}

.footer-social a:hover {
  background: linear-gradient(135deg, #c41e3a 0%, #8b1529 100%);
  color: white;
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(196, 30, 58, 0.3);
}
</style>

<footer class="footer">
  <div class="footer-container">
    <!-- Brand Section -->
    <div class="footer-section">
      <div class="footer-logo">
        <div class="logo-icon">EV</div>
        <span>E-VoiceSpartan</span>
      </div>
      <p class="footer-desc">
        Empowering communities through feedback and transparency. Your voice matters in building a better tomorrow.
      </p>
      <div class="footer-social">
        <a href="#" title="Facebook" aria-label="Facebook">
          <i class="bi bi-facebook"></i>
        </a>
        <a href="#" title="Twitter" aria-label="Twitter">
          <i class="bi bi-twitter"></i>
        </a>
        <a href="#" title="Instagram" aria-label="Instagram">
          <i class="bi bi-instagram"></i>
        </a>
        <a href="#" title="LinkedIn" aria-label="LinkedIn">
          <i class="bi bi-linkedin"></i>
        </a>
      </div>
    </div>

    <!-- Product Section -->
    <div class="footer-section">
      <h4>Product</h4>
      <ul>
        <li><a href="../homepage.php">Home</a></li>
        <li><a href="../complaint/complaint.php">File Complaint</a></li>
        <li><a href="../suggestion/suggestion.php">Share Suggestion</a></li>
        <li><a href="../profile/profile.php">My Profile</a></li>
      </ul>
    </div>

    <!-- Resources Section -->
    <div class="footer-section">
      <h4>Resources</h4>
      <ul>
        <li><a href="#">About Us</a></li>
        <li><a href="#">Contact Support</a></li>
        <li><a href="#">FAQ</a></li>
        <li><a href="#">Help Center</a></li>
      </ul>
    </div>

    <!-- Legal Section -->
    <div class="footer-section">
      <h4>Legal</h4>
      <ul>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms of Service</a></li>
        <li><a href="#">Cookie Policy</a></li>
        <li><a href="#">Disclaimer</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; <?php echo date('Y'); ?> E-VoiceSpartan. All rights reserved. Built with <i class="bi bi-heart-fill" style="color: #c41e3a;"></i> for the community.</p>
  </div>
</footer>