<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Adjust paths since this file is in /public/
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Subscription.php';

try {
    $subscription = new Subscription();
    $plans = $subscription->getPlans();
} catch (Exception $e) {
    $plans = []; // Fallback to empty if error
    error_log("Landing Page Plan Fetch Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    
    <?php 
    $pageTitle = "Enterprise Business Management Platform";
    $pageDescription = "Unify your organization with Acculynce. The operating system for modern business - Inventory, HR, Finance, and Sales in one platform.";
    require_once __DIR__ . '/../includes/public_meta.php'; 
    ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>

<body>
    <!-- Navigation -->
    <?php require_once __DIR__ . '/../includes/public_header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">
                        <span></span> v2.0 Now Available
                    </div>
                    <h1 class="hero-title">
                        The Operating System for <span class="gradient-text">Modern Business</span>
                    </h1>
                    <p class="hero-subtitle">
                        Unify your entire organization on one platform. Manage inventory, finance, HR, and sales with
                        enterprise-grade precision and clarity.
                    </p>
                    <div class="hero-buttons">
                        <a href="../modules/auth/register.php" class="btn btn-primary">
                            Start Free Trial <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="#features" class="btn btn-secondary">
                            View Demo
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="stat">
                            <div class="stat-number">10k+</div>
                            <div class="stat-label">Active Users</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">99.9%</div>
                            <div class="stat-label">Uptime SLA</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Support</div>
                        </div>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="dashboard-preview">
                        <div class="preview-screen">
                            <div class="ui-nav">
                                <div class="ui-dots">
                                    <span></span><span></span><span></span>
                                </div>
                            </div>
                            <div class="ui-content">
                                <div class="ui-sidebar"></div>
                                <div class="ui-main">
                                    <div class="ui-card" style="grid-column: span 2;"></div>
                                    <div class="ui-card"></div>
                                    <div class="ui-card"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trusted By -->
    <div class="trusted-by">
        <div class="container">
            <p class="trusted-text">TRUSTED BY INNOVATIVE COMPANIES</p>
            <div class="logos-grid">
                <div class="logo-item"><i class="fas fa-cube"></i> ACME Corp</div>
                <div class="logo-item"><i class="fas fa-bolt"></i> BoltShift</div>
                <div class="logo-item"><i class="fas fa-leaf"></i> GreenLeaf</div>
                <div class="logo-item"><i class="fas fa-globe"></i> GlobalTech</div>
            </div>
        </div>
    </div>

    <!-- Features Section (Bento Grid) -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Capabilities</span>
                <h2 class="section-title">Everything you need to scale</h2>
                <p class="section-subtitle">A complete suite of tools designed to work together seamlessly.</p>
            </div>

            <div class="features-container">
                <!-- Feature 1: Inventory -->
                <div class="feature-row">
                    <div class="feature-content">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h3 class="feature-title">Smart Inventory Management</h3>
                        <p class="feature-desc">
                            Stop guessing with your stock. Track inventory in real-time across multiple warehouses,
                            automate reordering, and predict demand with AI-driven insights.
                        </p>
                        <ul class="feature-checklist">
                            <li><i class="fas fa-check"></i> Multi-warehouse synchronization</li>
                            <li><i class="fas fa-check"></i> Barcode & QR code scanning</li>
                            <li><i class="fas fa-check"></i> Low stock alerts & auto-reorder</li>
                        </ul>
                    </div>
                    <div class="feature-visual">
                        <div class="visual-bg"></div>
                        <div class="visual-card">
                            <div class="f-ui-header">
                                <div class="f-ui-title"></div>
                                <div class="f-ui-actions">
                                    <div class="f-ui-btn"></div>
                                    <div class="f-ui-btn"></div>
                                </div>
                            </div>
                            <div class="f-ui-grid">
                                <div class="f-ui-stat">
                                    <div class="f-ui-stat-val"></div>
                                    <div class="f-ui-stat-label"></div>
                                </div>
                                <div class="f-ui-stat">
                                    <div class="f-ui-stat-val" style="background: #10b981;"></div>
                                    <div class="f-ui-stat-label"></div>
                                </div>
                                <div class="f-ui-stat">
                                    <div class="f-ui-stat-val" style="background: #f59e0b;"></div>
                                    <div class="f-ui-stat-label"></div>
                                </div>
                            </div>
                            <div class="f-ui-list">
                                <div class="f-ui-row">
                                    <div class="f-ui-avatar"></div>
                                    <div class="f-ui-line"></div>
                                </div>
                                <div class="f-ui-row">
                                    <div class="f-ui-avatar"></div>
                                    <div class="f-ui-line"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature 2: Finance -->
                <div class="feature-row">
                    <div class="feature-content">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h3 class="feature-title">Financial Clarity & Compliance</h3>
                        <p class="feature-desc">
                            Keep your finances in check without the headache. Automate bookkeeping, generate
                            GST-compliant invoices, and get a real-time view of your cash flow.
                        </p>
                        <ul class="feature-checklist">
                            <li><i class="fas fa-check"></i> Automated GST calculations</li>
                            <li><i class="fas fa-check"></i> Profit & Loss statements</li>
                            <li><i class="fas fa-check"></i> Expense tracking & categorization</li>
                        </ul>
                    </div>
                    <div class="feature-visual">
                        <div class="visual-bg"
                            style="background: radial-gradient(circle, rgba(14, 165, 233, 0.1) 0%, transparent 70%);">
                        </div>
                        <div class="visual-card">
                            <div class="f-ui-header">
                                <div class="f-ui-title" style="width: 150px;"></div>
                            </div>
                            <div
                                style="height: 150px; background: var(--bg-light); border-radius: 12px; display: flex; align-items: flex-end; justify-content: space-around; padding: 20px;">
                                <div style="width: 15%; height: 40%; background: #cbd5e1; border-radius: 4px;"></div>
                                <div style="width: 15%; height: 60%; background: #cbd5e1; border-radius: 4px;"></div>
                                <div style="width: 15%; height: 30%; background: #cbd5e1; border-radius: 4px;"></div>
                                <div
                                    style="width: 15%; height: 80%; background: var(--secondary-color); border-radius: 4px;">
                                </div>
                                <div style="width: 15%; height: 50%; background: #cbd5e1; border-radius: 4px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature 3: HR & Payroll -->
                <div class="feature-row">
                    <div class="feature-content">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="feature-title">People Management Simplified</h3>
                        <p class="feature-desc">
                            Build a happier workplace. Manage the entire employee lifecycle from onboarding to
                            offboarding, including attendance, leaves, and payroll processing.
                        </p>
                        <ul class="feature-checklist">
                            <li><i class="fas fa-check"></i> One-click payroll processing</li>
                            <li><i class="fas fa-check"></i> Self-service employee portal</li>
                            <li><i class="fas fa-check"></i> Automated tax deductions</li>
                        </ul>
                    </div>
                    <div class="feature-visual">
                        <div class="visual-bg"
                            style="background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);">
                        </div>
                        <div class="visual-card">
                            <div class="f-ui-list">
                                <div class="f-ui-row">
                                    <div class="f-ui-avatar" style="background: #fee2e2;"></div>
                                    <div class="f-ui-line"></div>
                                    <div
                                        style="width: 20px; height: 20px; border-radius: 50%; background: #dcfce7; color: #166534; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                                <div class="f-ui-row">
                                    <div class="f-ui-avatar" style="background: #e0e7ff;"></div>
                                    <div class="f-ui-line"></div>
                                    <div
                                        style="width: 20px; height: 20px; border-radius: 50%; background: #dcfce7; color: #166534; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                                <div class="f-ui-row">
                                    <div class="f-ui-avatar" style="background: #fef3c7;"></div>
                                    <div class="f-ui-line"></div>
                                    <div
                                        style="width: 20px; height: 20px; border-radius: 50%; background: #dcfce7; color: #166534; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="pricing">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Pricing</span>
                <h2 class="section-title">Transparent pricing for everyone</h2>
                <p class="section-subtitle">Start small and scale as you grow. No hidden fees.</p>
            </div>

            <div class="pricing-toggle">
                <span class="toggle-label active" id="monthlyLabel">Monthly</span>
                <div class="switch" id="billingToggle"></div>
                <span class="toggle-label" id="annualLabel">Annual <span class="discount-chip">-20%</span></span>
            </div>

            <div class="pricing-cards">
                <?php if (empty($plans)): ?>
                    <p class="text-center">No plans available at the moment.</p>
                <?php else: ?>
                    <?php foreach ($plans as $plan): ?>
                        <?php 
                            $isPopular = $plan['plan_name'] === 'Professional';
                            $features = json_decode($plan['features'], true) ?? [];
                            $monthlyPrice = number_format($plan['monthly_price'], 0);
                            $annualPrice = number_format($plan['annual_price'] / 12, 0); // Show monthly equivalent
                            $actionText = $plan['plan_name'] === 'Enterprise' ? 'Contact Sales' : 'Start Free Trial';
                        ?>
                        <div class="price-card <?php echo $isPopular ? 'popular' : ''; ?>">
                            <?php if ($isPopular): ?>
                                <div class="popular-badge">Most Popular</div>
                            <?php endif; ?>
                            
                            <h3 class="plan-name"><?php echo htmlspecialchars($plan['plan_name']); ?></h3>
                            <p class="plan-desc">
                                <?php 
                                    echo match($plan['plan_name']) {
                                        'Starter' => 'For small teams just getting started.',
                                        'Professional' => 'For growing businesses needing more power.',
                                        'Enterprise' => 'For large organizations requiring scale.',
                                        default => 'Comprehensive features for your business.'
                                    };
                                ?>
                            </p>
                            
                            <div class="price">
                                <span class="currency">₹</span>
                                <span class="amount" 
                                      data-monthly="<?php echo $monthlyPrice; ?>" 
                                      data-annual="<?php echo $annualPrice; ?>">
                                    <?php echo $monthlyPrice; ?>
                                </span>
                                <span class="period">/mo</span>
                            </div>
                            
                            <ul class="features-list">
                                <?php foreach ($features as $feature): ?>
                                    <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <a href="../modules/auth/register.php" class="btn-plan <?php echo !$isPopular ? 'btn-outline' : ''; ?>">
                                <?php echo $actionText; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-bg"></div>
        <div class="container">
            <div class="cta-content">
                <h2>Ready to modernize your business?</h2>
                <p>Join thousands of forward-thinking companies running on Acculynce.</p>
                <a href="../modules/auth/register.php" class="btn btn-primary btn-large">
                    Start Your 14-Day Free Trial
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php require_once __DIR__ . '/../includes/public_footer.php'; ?>
    <script src="assets/js/landing.js"></script>
</body>

</html>
