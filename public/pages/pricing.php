<?php
require_once '../../config/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Subscription.php';

try {
    $subscription = new Subscription();
    $plans = $subscription->getPlans();
} catch (Exception $e) {
    $plans = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php 
    $pageTitle = "Pricing";
    $pageDescription = "Transparent pricing for every stage of your business. Start with a free trial and scale as you grow.";
    require_once '../../includes/public_meta.php'; 
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/landing.css">
    <style>
        .page-header {
            padding: 120px 0 60px;
            text-align: center;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }
        .page-title {
            font-size: 3rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 20px;
        }
        .page-subtitle {
            font-size: 1.25rem;
            color: #64748b;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/public_header.php'; ?>

    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Simple, Transparent Pricing</h1>
            <p class="page-subtitle">Choose the perfect plan for your business. No hidden fees. Cancel anytime.</p>
        </div>
    </header>

    <section class="pricing" style="padding-top: 0;">
        <div class="container">
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
                            $annualPrice = number_format($plan['annual_price'] / 12, 0); 
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
                            
                            <a href="../../modules/auth/register.php" class="btn-plan <?php echo !$isPopular ? 'btn-outline' : ''; ?>">
                                <?php echo $actionText; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php require_once '../../includes/public_footer.php'; ?>
    <script src="../../public/assets/js/landing.js"></script>
</body>
</html>
