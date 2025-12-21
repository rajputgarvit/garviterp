<?php
// Ensure BASE_URL is defined if not already (safeguard)
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
<!-- Navigation -->
<nav class="navbar">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>public/landing.php" class="nav-brand">
            <img src="<?php echo BASE_URL; ?>public/assets/images/logo.svg" alt="Acculynce Systems"
                style="max-width: 150px; max-height: 60px; height: auto; mix-blend-mode: multiply;" />
        </a>
        <div class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </div>
        <ul class="nav-menu" id="navMenu">
            <li><a href="<?php echo BASE_URL; ?>public/pages/features.php">Features</a></li>
            <li><a href="<?php echo BASE_URL; ?>public/pages/pricing.php">Pricing</a></li>
            <li><a href="<?php echo BASE_URL; ?>public/contact.php">Contact</a></li>
            <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                <li><a href="<?php echo BASE_URL; ?>modules/dashboard/index.php" class="btn-login">Go to Dashboard</a></li>
            <?php else: ?>
                <li><a href="<?php echo BASE_URL; ?>modules/auth/login.php" class="btn-login">Sign In</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
