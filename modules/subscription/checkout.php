<?php
// session_start(); // Handled in config.php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Subscription.php';
require_once '../../classes/Payment.php';

$auth = new Auth();
$db = Database::getInstance();
$subscription = new Subscription();
$payment = new Payment();

// Check if plan is selected
if (!isset($_SESSION['selected_plan']) && !isset($_GET['plan'])) {
    header('Location: select-plan.php');
    exit;
}

// Get Current User & Company
$user = $auth->getCurrentUser();

// Handle Pending User (New Registration)
if (!$user && isset($_SESSION['pending_user_id'])) {
    if ($auth->forceLogin($_SESSION['pending_user_id'])) {
        $user = $auth->getCurrentUser(); // Refresh user after login
    }
}

if (!$user) {
    // Not logged in and no pending registration found
    header('Location: ../auth/register.php');
    exit;
}

$userId = $user['id'];
$companyId = $user['company_id'];

if (!$companyId) {
    die("Company ID not found. Please contact support.");
}

// Check plan details
$planName = $_GET['plan'] ?? $_SESSION['selected_plan'] ?? '';
$billingCycle = $_GET['billing'] ?? $_SESSION['selected_billing'] ?? 'monthly';

// Validate Plan
$validPlans = $subscription->getPlans();
$planValid = false;
$price = 0;

foreach ($validPlans as $p) {
    if ($p['plan_name'] === $planName) {
        $planValid = true;
        $plan = $p;
        $price = ($billingCycle === 'annual') ? $p['annual_price'] : $p['monthly_price'];
        break;
    }
}

if (!$planValid) {
    header('Location: select-plan.php');
    exit;
}

// Check if company has used trial
$hasUsedTrial = $subscription->hasUsedTrial($companyId);
$isTrialMode = (!$hasUsedTrial && isset($_GET['trial']) && $_GET['trial'] == '1');


// -------------------------------------------------------------------------
// PAYTM CALLBACK HANDLING
// -------------------------------------------------------------------------
if (isset($_POST["CHECKSUMHASH"])) {
    $paytmChecksum = $_POST["CHECKSUMHASH"];
    $isValidChecksum = $payment->verifyPaytmSignature($_POST, $paytmChecksum);

    if ($isValidChecksum == "TRUE" && $_POST["STATUS"] == "TXN_SUCCESS") {
        
        $orderId = $_POST['ORDERID'];
        $txnId = $_POST['TXnid'];
        $txnAmount = $_POST['TXNAMOUNT'];
        
        // Success
         // Create subscription (Active immediately)
        $subscriptionId = $subscription->createSubscription($companyId, $planName, $billingCycle, 'active', $userId);
        
        // Record transaction
        $payment->recordTransaction($subscriptionId, [
            'txn_id' => $txnId,
            'order_id' => $orderId,
            'amount' => $txnAmount,
            'currency' => 'INR',
            'status' => 'success',
            'method' => 'Paytm'
        ]);
        
        $subscription->activateSubscription($subscriptionId);
        
        unset($_SESSION['pending_user_id']);
        unset($_SESSION['selected_plan']);
        unset($_SESSION['selected_billing']);
        
        header('Location: payment-success.php?subscription_id=' . $subscriptionId);
        exit;

    } else {
        // Failed
        $errorMsg = $_POST['RESPMSG'] ?? "Payment verification failed";
        header('Location: payment-failed.php?error=' . urlencode($errorMsg));
        exit;
    }
}

// -------------------------------------------------------------------------
// PREPARE PAYTM PARAMETERS (For Form)
// -------------------------------------------------------------------------
if ($hasUsedTrial && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Generate Order ID
    $orderId = "ORDS" . rand(10000,99999999);
    $custId = "CUST" . $userId;
    
    // Get Params
    $paytmParams = $payment->getPaytmParams($orderId, $price, $custId, $user['email'] ?? '', '');
    
    // Generate Checksum
    $paytmChecksum = $payment->generatePaytmSignature($paytmParams);
    
    // Initial Form URL
    $transactionURL = "https://securegw-stage.paytm.in/order/process"; 
    // For Production use: https://securegw.paytm.in/order/process
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Acculynce</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Include Landing CSS -->
    <link rel="stylesheet" href="../../public/assets/css/landing.css">
    <style>
        body {
            background-color: var(--bg-light);
            padding-top: 80px;
        }

        .checkout-container {
            max-width: 1000px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 40px;
            padding: 0 20px;
        }

        .checkout-main, .checkout-sidebar {
            background: white;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .checkout-sidebar {
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--text-secondary);
            margin-bottom: 30px;
        }

        .trial-badge {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            padding: 10px 20px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            font-weight: 500;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .plan-summary-card {
            background: var(--bg-light);
            padding: 24px;
            border-radius: 16px;
            margin-bottom: 30px;
        }

        .plan-summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .plan-summary-header h3 {
            font-size: 1.2rem;
            color: var(--primary-color);
            font-weight: 700;
        }

        .features-list {
            list-style: none;
        }

        .features-list li {
            padding: 8px 0;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
        }

        .features-list i {
            color: var(--success-color);
        }

        .btn-pay {
            width: 100%;
            padding: 16px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-pay:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-pay.trial {
            background: var(--success-color);
        }
        
        .btn-pay.trial:hover {
             background: #059669; /* Darker green */
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .summary-row.total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .secure-badge {
            text-align: center;
            margin-top: 20px;
            color: var(--text-light);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        @media (max-width: 900px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            .checkout-sidebar {
                position: static;
                order: -1; 
                order: 1; 
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <?php require_once '../../includes/public_header.php'; ?>

    <div class="checkout-container">
        <div class="checkout-main">
            <h1>Complete Your Order</h1>
            <p class="subtitle">You're just one step away from modernizing your business.</p>

            <div class="trial-badge">
                <i class="fas fa-gift"></i>
                <span>14-Day Free Trial - No commitment, cancel anytime.</span>
            </div>

            <div class="plan-summary-card">
                <div class="plan-summary-header">
                    <h3><?php echo htmlspecialchars($plan['plan_name']); ?> Plan</h3>
                    <span style="font-weight: 500; color: var(--text-secondary);"><?php echo $billingCycle === 'annual' ? 'Annual' : 'Monthly'; ?> Billing</span>
                </div>
                
                <ul class="features-list">
                    <?php 
                    $features = json_decode($plan['features'], true);
                    foreach (array_slice($features, 0, 5) as $feature): 
                    ?>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($feature); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h4 style="margin-bottom: 15px; color: var(--text-primary);">Secure Payment</h4>
                <p style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.6;">
                    We use industry-standard encryption to protect your data. Your payment information is processed securely by <strong>Paytm</strong>.
                </p>
            </div>

             <div class="secure-badge">
                <i class="fas fa-lock" style="color: var(--success-color);"></i>
                SSL Encrypted Payment
            </div>
        </div>

        <div class="checkout-sidebar">
            <h3 style="margin-bottom: 20px; font-size: 1.2rem; color: var(--text-primary);">Order Summary</h3>

            <div class="summary-row">
                <span><?php echo htmlspecialchars($plan['plan_name']); ?> Plan</span>
                <span>₹<?php echo number_format($price, 2); ?></span>
            </div>

            <div class="summary-row">
                <span>Billing Cycle</span>
                <span><?php echo ucfirst($billingCycle); ?></span>
            </div>
            
            <?php if (!$subscription->hasUsedTrial($userId)): ?>
            <div class="summary-row">
                <span>Trial Period</span>
                <span>14 Days</span>
            </div>
            <?php endif; ?>

            <div class="summary-row total">
                <span>Due Today</span>
                <?php if ($subscription->hasUsedTrial($userId)): ?>
                    <span>₹<?php echo number_format($price, 2); ?></span>
                <?php else: ?>
                    <span style="color: var(--success-color);">₹0.00</span>
                <?php endif; ?>
            </div>
            
            <?php if (!$subscription->hasUsedTrial($userId)): ?>
            <div style="margin-top: 10px; font-size: 0.85rem; color: var(--text-secondary); text-align: right;">
                Then ₹<?php echo number_format($price, 2); ?>/<?php echo $billingCycle === 'monthly' ? 'mo' : 'yr'; ?>
            </div>
            <?php endif; ?>

            <?php if ($hasUsedTrial): ?>
                <!-- Upgrading / Re-subscribing: Payment Only -->
                <button class="btn-pay" onclick="payWithPaytm()">
                    <i class="fas fa-credit-card"></i>
                    Pay ₹<?php echo number_format($price, 2); ?>
                </button>
            <?php else: ?>
                <!-- New User: Free Trial -->
                <button class="btn-pay trial" onclick="startTrial()">
                    <i class="fas fa-rocket"></i>
                    Start Free Trial
                </button>
            <?php endif; ?>
            
            <div style="margin-top: 20px; text-align: center; font-size: 0.8rem; color: var(--text-light);">
                By continuing, you agree to our <a href="#" style="color: var(--primary-color);">Terms of Service</a>.
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once '../../includes/public_footer.php'; ?>

    <?php if ($hasUsedTrial && isset($transactionURL)): ?>
    <!-- HIDDEN FORM FOR PAYTM -->
    <form method="post" action="<?php echo $transactionURL; ?>" name="f1" id="paytmForm">
        <?php foreach($paytmParams as $name => $value) {
            echo '<input type="hidden" name="' . $name .'" value="' . $value . '">';
        } ?>
        <input type="hidden" name="CHECKSUMHASH" value="<?php echo $paytmChecksum ?>">
    </form>
    <?php endif; ?>

    <script>
        function startTrial() {
            const plan = "<?php echo urlencode($planName); ?>";
            const billing = "<?php echo urlencode($billingCycle); ?>";
            window.location.href = 'create-trial-subscription.php?plan=' + plan + '&billing=' + billing;
        }

        function payWithPaytm() {
            document.getElementById("paytmForm").submit();
        }
    </script>
</body>
</html>
