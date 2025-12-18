<?php
// session_start(); // Handled in config.php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Subscription.php';

$auth = new Auth();
$db = Database::getInstance();
$subscription = new Subscription();

// Check if user is coming from registration
if (!isset($_SESSION['pending_user_id']) && !$auth->isLoggedIn()) {
    header('Location: ../auth/register.php');
    exit;
}

// Get user ID
$userId = $_SESSION['pending_user_id'] ?? $_SESSION['user_id'] ?? null;

if (!$userId) {
    header('Location: ../auth/register.php');
    exit;
}

// Check verification status
$user = $db->fetchOne("SELECT email_verified FROM users WHERE id = ?", [$userId]);
if (!$user || $user['email_verified'] == 0) {
    // Determine email for session if missing
    if (!isset($_SESSION['pending_user_email'])) {
        $userData = $db->fetchOne("SELECT email FROM users WHERE id = ?", [$userId]);
        if ($userData) $_SESSION['pending_user_email'] = $userData['email'];
    }
    header('Location: ../auth/verification-pending.php');
    exit;
}

// Get company ID
$companyId = null;
if (isset($user['company_id'])) {
    $companyId = $user['company_id'];
} else {
    // If pending, fetch from DB
    $u = $db->fetchOne("SELECT company_id FROM users WHERE id = ?", [$userId]);
    $companyId = $u['company_id'] ?? null;
}

// Get all plans
$plans = $subscription->getPlans();
$hasUsedTrial = $companyId ? $subscription->hasUsedTrial($companyId) : false;

// Handle plan selection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $planName = $_POST['plan_name'] ?? '';
    $billingCycle = $_POST['billing_cycle'] ?? 'monthly';
    
    // Store selection in session
    $_SESSION['selected_plan'] = $planName;
    $_SESSION['selected_billing'] = $billingCycle;
    
    // Redirect to checkout
    // Redirect to checkout
    header("Location: checkout.php?plan=" . urlencode($planName) . "&billing=" . urlencode($billingCycle));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Plan - Acculynce</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Include Landing CSS -->
    <link rel="stylesheet" href="../../public/assets/css/landing.css">
    <style>
        /* Specific overrides for selection page */
        body {
            background-color: var(--bg-light);
            padding-top: 80px; /* Space for fixed navbar */
        }
        .page-header {
            text-align: center;
            padding: 60px 20px 40px;
        }
        .page-header h1 {
            font-size: 2.5rem;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        .continue-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: white;
            padding: 20px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            z-index: 100;
        }
        .continue-bar.visible {
            transform: translateY(0);
        }
        .plan-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .plan-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
        }
        .plan-card.selected {
            border-color: var(--primary-color);
            background-color: #f5f3ff; /* Very light primary tint */
            box-shadow: 0 0 0 2px var(--primary-color);
        }
        /* Hide annual price by default */
        .price .amount[data-annual] {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php require_once '../../includes/public_header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>Choose the Perfect Plan</h1>
            <p class="section-subtitle">Start your 14-day free trial today. Scale as you grow.</p>
        </div>

        <div class="pricing-toggle">
            <span class="toggle-label active" id="monthlyLabel">Monthly</span>
            <div class="switch" id="billingToggle"></div>
            <span class="toggle-label" id="annualLabel">Annual <span class="discount-chip">-20%</span></span>
        </div>

        <form method="POST" id="planForm">
            <input type="hidden" name="plan_name" id="selectedPlanInput">
            <input type="hidden" name="billing_cycle" id="billingCycleInput" value="monthly">
            
            <div class="pricing-cards">
                <?php foreach ($plans as $plan): ?>
                    <?php 
                        $isPopular = $plan['plan_name'] === 'Professional';
                        $features = json_decode($plan['features'], true) ?? [];
                        $monthlyPrice = number_format($plan['monthly_price'], 0);
                        $annualPrice = number_format($plan['annual_price'] / 12, 0);
                    ?>
                    <div class="price-card plan-card <?php echo $isPopular ? 'popular' : ''; ?>" 
                         onclick="selectPlan('<?php echo $plan['plan_name']; ?>', this)"
                         data-plan="<?php echo $plan['plan_name']; ?>">
                        
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
                            <span class="amount monthly" data-value="<?php echo $monthlyPrice; ?>"><?php echo $monthlyPrice; ?></span>
                            <span class="amount annual" data-value="<?php echo $annualPrice; ?>" style="display: none;"><?php echo $annualPrice; ?></span>
                            <span class="period">/mo</span>
                        </div>
                        
                        <ul class="features-list">
                            <?php foreach ($features as $feature): ?>
                                <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <button type="button" class="btn btn-primary select-btn" style="width: 100%;">
                            Select Plan
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="continue-bar" id="continueBar">
                <div style="font-weight: 600; font-size: 1.1rem;">
                    Selected: <span id="summaryPlan" style="color: var(--primary-color);">None</span> 
                    (<span id="summaryCycle">Monthly</span>)
                </div>
                <button type="submit" class="btn btn-primary">
                    <?php echo $hasUsedTrial ? 'Proceed to Pay' : 'Start Free Trial'; ?> <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <?php require_once '../../includes/public_footer.php'; ?>

    <script>
        let currentBilling = 'monthly';
        let currentPlan = null;

        const toggle = document.getElementById('billingToggle');
        const monthlyLabel = document.getElementById('monthlyLabel');
        const annualLabel = document.getElementById('annualLabel');
        const billingInput = document.getElementById('billingCycleInput');
        
        // Handle Billing Toggle
        if (toggle) {
            toggle.addEventListener('click', () => {
                toggle.classList.toggle('active');
                
                if (toggle.classList.contains('active')) {
                    currentBilling = 'annual';
                    monthlyLabel.classList.remove('active');
                    annualLabel.classList.add('active');
                    
                    // Show Annual Prices
                    document.querySelectorAll('.amount.monthly').forEach(el => el.style.display = 'none');
                    document.querySelectorAll('.amount.annual').forEach(el => el.style.display = 'inline');
                } else {
                    currentBilling = 'monthly';
                    monthlyLabel.classList.add('active');
                    annualLabel.classList.remove('active');
                    
                    // Show Monthly Prices
                    document.querySelectorAll('.amount.monthly').forEach(el => el.style.display = 'inline');
                    document.querySelectorAll('.amount.annual').forEach(el => el.style.display = 'none');
                }
                
                billingInput.value = currentBilling;
                document.getElementById('summaryCycle').textContent = currentBilling.charAt(0).toUpperCase() + currentBilling.slice(1);
            });
        }

        function selectPlan(planName, cardElement) {
            currentPlan = planName;
            document.getElementById('selectedPlanInput').value = planName;

            // Reset all cards
            document.querySelectorAll('.plan-card').forEach(card => {
                card.classList.remove('selected');
                card.querySelector('.select-btn').textContent = 'Select Plan';
                card.querySelector('.select-btn').classList.remove('btn-success'); 
                card.querySelector('.select-btn').classList.add('btn-primary');
            });

            // Select clicked card
            cardElement.classList.add('selected');
            const btn = cardElement.querySelector('.select-btn');
            btn.textContent = 'Selected';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success'); // Assuming success color class exists or default bootstrap style

            // Show continue bar
            document.getElementById('continueBar').classList.add('visible');
            document.getElementById('summaryPlan').textContent = planName;
        }
    </script>
</body>
</html>
