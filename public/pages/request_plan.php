<?php
require_once '../../config/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Subscription.php';

$auth = new Auth();
$isLoggedIn = $auth->isLoggedIn();
$user = $isLoggedIn ? $auth->getCurrentUser() : null;

$planName = $_GET['plan'] ?? '';
$billingCycle = $_GET['cycle'] ?? 'monthly';

if (empty($planName)) {
    header('Location: pricing.php');
    exit;
}

$pageTitle = "Confirm Plan Request";
require_once '../../includes/public_meta.php';

// Fetch plan details for pricing
$db = Database::getInstance();
$planDetails = $db->fetchOne("SELECT * FROM subscription_plans WHERE plan_name = ? AND is_active = 1", [$planName]);

$priceDisplay = 'N/A';
$totalDisplay = 'N/A';

if ($planDetails) {
    if ($billingCycle === 'annual') {
        $monthlyEquivalent = number_format($planDetails['annual_price'] / 12, 0);
        $totalPrice = number_format($planDetails['annual_price'], 0);
        $priceDisplay = "₹{$monthlyEquivalent} / month";
        $totalDisplay = "₹{$totalPrice} / year";
    } else {
        $price = number_format($planDetails['monthly_price'], 0);
        $priceDisplay = "₹{$price} / month";
        $totalDisplay = "₹{$price} / month";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Request - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/landing.css">
    <!-- Bootstrap 5 for Layout -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; }
        .request-card {
            max-width: 600px;
            margin: 80px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #4f46e5 0%, #0ea5e9 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .card-body { padding: 40px; }
        .plan-summary {
            background: #f1f5f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #64748b;
        }
        .summary-row.total {
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
            color: #0f172a;
        }
        .btn-confirm {
            width: 100%;
            padding: 12px;
            font-weight: 600;
            border-radius: 6px;
            background: #4f46e5;
            border: none;
            color: white;
        }
        .btn-confirm:hover { background: #4338ca; }
        .form-label { font-size: 0.9rem; font-weight: 500; color: #475569; }
        .form-control { padding: 10px; border-color: #e2e8f0; }
        .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1); }
    </style>
</head>
<body>
    <?php require_once '../../includes/public_header.php'; ?>

    <div class="container">
        <div class="request-card">
            <div class="card-header">
                <h2 class="mb-0">Request <?php echo htmlspecialchars($planName); ?> Plan</h2>
                <p class="mb-0 opacity-75">
                    <?php echo $isLoggedIn ? 'Review your subscription request' : 'Enter your details to request this plan'; ?>
                </p>
            </div>
            
            <div class="card-body">
                <div class="plan-summary">
                    <div class="summary-row">
                        <span>Requested Plan</span>
                        <strong class="text-dark"><?php echo htmlspecialchars($planName); ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Billing Cycle</span>
                        <strong class="text-dark"><?php echo ucfirst(htmlspecialchars($billingCycle)); ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Price</span>
                        <strong class="text-dark"><?php echo $priceDisplay; ?></strong>
                    </div>
                    <div class="summary-row total">
                        <span>Total Pay</span>
                        <strong class="text-primary"><?php echo $totalDisplay; ?></strong>
                    </div>
                    <?php if ($isLoggedIn): ?>
                    <div class="summary-row mt-2 pt-2 border-top">
                        <span>Account</span>
                        <strong class="text-dark"><?php echo htmlspecialchars($user['company_name']); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($isLoggedIn): ?>
                    <!-- Logged In User Form -->
                    <form id="userRequestForm">
                        <input type="hidden" name="plan_name" value="<?php echo htmlspecialchars($planName); ?>">
                        <input type="hidden" name="billing_cycle" value="<?php echo htmlspecialchars($billingCycle); ?>">
                        
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle"></i> Does not charge you immediately. Our team will review and approve.
                        </div>

                        <button type="submit" class="btn-confirm">
                            Confirm Request
                        </button>
                    </form>
                <?php else: ?>
                    <!-- Guest Form -->
                    <form id="guestRequestForm">
                        <input type="hidden" name="plan_name" value="<?php echo htmlspecialchars($planName); ?>">
                        <input type="hidden" name="billing_cycle" value="<?php echo htmlspecialchars($billingCycle); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="John Doe">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Work Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="john@company.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" required placeholder="Acme Inc.">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number (Optional)</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
                        </div>

                        <button type="submit" class="btn-confirm">
                            Submit Request
                        </button>
                        <div class="text-center mt-3">
                            <small class="text-muted">Already have an account? <a href="../../modules/auth/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Sign In</a></small>
                        </div>
                    </form>
                <?php endif; ?>
                
                <a href="pricing.php" class="btn btn-link w-100 mt-2 text-muted" style="text-decoration: none;">Cancel</a>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <div class="mb-3 text-success">
                    <i class="fas fa-check-circle fa-4x"></i>
                </div>
                <h3>Request Sent!</h3>
                <p class="text-muted">We have received your request. Our team will contact you shortly to finalize your subscription.</p>
                <a href="<?php echo $isLoggedIn ? '../../modules/dashboard/index.php' : 'pricing.php'; ?>" class="btn btn-primary mt-3">
                    <?php echo $isLoggedIn ? 'Go to Dashboard' : 'Back to Pricing'; ?>
                </a>
            </div>
        </div>
    </div>

    <?php require_once '../../includes/public_footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const userForm = document.getElementById('userRequestForm');
        const guestForm = document.getElementById('guestRequestForm');
        
        const handleFormSubmit = (e, url) => {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;

            const formData = new FormData(form);

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    new bootstrap.Modal(document.getElementById('successModal')).show();
                    if(guestForm) guestForm.reset();
                } else {
                    alert('Error: ' + data.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        };

        if (userForm) {
            userForm.addEventListener('submit', (e) => handleFormSubmit(e, '../../modules/subscriptions/request.php'));
        }

        if (guestForm) {
            guestForm.addEventListener('submit', (e) => handleFormSubmit(e, '../../modules/subscriptions/request_guest.php'));
        }
    </script>
        <script src="../../public/assets/js/landing.js"></script>

</body>
</html>
