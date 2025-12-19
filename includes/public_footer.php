<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <a href="#" class="nav-brand footer-brand">
                    <img src="<?php echo BASE_URL; ?>public/assets/images/logo.svg"
                        alt="Acculynce Systems"
                        style="max-width: 150px; max-height: 60px; height: auto; mix-blend-mode: multiply;" />
                </a>
                <p class="footer-desc">
                    The complete operating system for modern businesses. Built for scale, designed for simplicity.
                </p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                </div>
            </div>
            <!-- ... checks footer links ... -->
            <div class="footer-col">
                <h4>Product</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>public/pages/features.php">Features</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/pages/pricing.php">Pricing</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/pages/security.php">Security</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/pages/roadmap.php">Roadmap</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Company</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>public/pages/about.php">About</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/pages/careers.php">Careers</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/pages/blog.php">Blog</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Resources</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>public/pages/documentation.php">Documentation</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/pages/api-reference.php">API Reference</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/pages/community.php">Community</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/pages/help-center.php">Help Center</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <span id="currentYear"></span> Acculynce Inc. All rights reserved.</p>
            <div class="legal-links">
                <a href="privacy-policy.html" style="margin-right: 20px;">Privacy Policy</a>
                <a href="terms-of-service.html">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<script>
    document.getElementById('currentYear').textContent = new Date().getFullYear();
</script>
