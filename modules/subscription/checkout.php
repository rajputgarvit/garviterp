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
// Check if plan is selected
if (!isset($_SESSION['selected_plan']) && !isset($_GET['plan'])) {
    header('Location: select-plan.php');
    exit;
}

// Get Current User & Company
$user = $auth->getCurrentUser();
$userId = $user['id'];
$companyId = $user['company_id'];

if (!$companyId) {
    die("Company ID not found. Please contact support.");
}

// Check plan details
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

// Handle Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_payment_id'])) {
    $paymentId = $_POST['razorpay_payment_id'];
    $orderId = $_POST['razorpay_order_id'];
    $signature = $_POST['razorpay_signature'];

    try {
        if ($payment->verifyPaymentSignature($orderId, $paymentId, $signature)) {
            // Create subscription (Active immediately)
            // Pass company_id as first arg, user_id as last arg
            $subscriptionId = $subscription->createSubscription($companyId, $planName, $billingCycle, 'active', $userId);
            
            // Record transaction
            $payment->recordTransaction($subscriptionId, [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'amount' => $price,
                'currency' => 'INR',
                'status' => 'success',
                'method' => $_POST['payment_method'] ?? 'razorpay'
            ]);
            
            // Activate subscription (already active but updates razorpay/dates if needed)
            $subscription->activateSubscription($subscriptionId);
            
            // Clear session variables but keep user_id if logged in
            unset($_SESSION['pending_user_id']);
            unset($_SESSION['selected_plan']);
            unset($_SESSION['selected_billing']);
            
            // Redirect to success page
            header('Location: payment-success.php?subscription_id=' . $subscriptionId);
            exit;
        } else {
            throw new Exception("Payment verification failed");
        }
    } catch (Exception $e) {
        header('Location: payment-failed.php?error=' . urlencode($e->getMessage()));
        exit;
    }
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
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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
                order: -1; /* Show summary first on mobile? Or maybe keep it below. Let's keep distinct. */
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
                    We use industry-standard encryption to protect your data. Your payment information is processed securely by Razorpay.
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
                <button class="btn-pay" onclick="initiatePayment()">
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

    <form method="POST" id="paymentForm">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
        <input type="hidden" name="payment_method" id="payment_method">
    </form>

    <script>
        function startTrial() {
            const plan = "<?php echo urlencode($planName); ?>";
            const billing = "<?php echo urlencode($billingCycle); ?>";
            window.location.href = 'create-trial-subscription.php?plan=' + plan + '&billing=' + billing;
        }

        // Razorpay payment
        function initiatePayment() {
            var options = {
                "key": "<?php echo $payment->getRazorpayKey(); ?>",
                "amount": <?php echo $price * 100; ?>,
                "currency": "INR",
                "name": "Acculynce",
                "description": "<?php echo $plan['plan_name']; ?> Plan Subscription",
                "handler": function (response){
                    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                    document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                    document.getElementById('razorpay_signature').value = response.razorpay_signature;
                    document.getElementById('paymentForm').submit();
                },
                "prefill": {
                    "email": "<?php echo $_SESSION['pending_user_email'] ?? $_SESSION['email'] ?? ''; ?>"
                },
                "theme": {
                    "color": "#4f46e5"
                }
            };
            var rzp = new Razorpay(options);
            rzp.on('payment.failed', function (response){
                alert("Payment Failed: " + response.error.description);
            });
            rzp.open();
        }
    </script>
</body>
</html>
