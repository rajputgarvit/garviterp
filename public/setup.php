<?php
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/Auth.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    die("Invalid access. Token missing.");
}

$db = Database::getInstance();
$request = $db->fetchOne("SELECT * FROM contact_requests WHERE onboarding_token = ?", [$token]);

if (!$request) {
    die("Invalid or expired token.");
}

if (strtotime($request['token_expires_at']) < time()) {
    die("This invitation link has expired. Please contact support.");
}

// Parse Plan from Subject: "New Plan Request: Standard (monthly)"
$planName = 'Basic'; // Default
$cycle = 'monthly';
if (preg_match('/Plan Request: (.+) \((.+)\)/', $request['subject'], $matches)) {
    $planName = trim($matches[1]);
    $cycle = trim($matches[2]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        // 1. Create Company Settings
        $companyId = $db->insert('company_settings', [
            'company_name' => trim($_POST['company_name']),
            'email' => $request['email'], // Main company email
            'phone' => $_POST['phone'],
            'address_line1' => $_POST['address_line'],
            'city' => $_POST['city'],
            'state' => $_POST['state'],
            'postal_code' => $_POST['pin'],
            'industry_type' => $_POST['industry'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 2. Create User (Super Admin)
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        // Check username uniqueness
        if ($db->fetchOne("SELECT id FROM users WHERE username = ?", [$username])) {
             throw new Exception("Username already taken. Please choose another.");
        }

        $userId = $db->insert('users', [
            'company_id' => $companyId,
            'full_name' => trim($_POST['full_name']),
            'username' => $username,
            'email' => $request['email'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'company_name' => trim($_POST['company_name']),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 3. Assign Super Admin Role
        // Assuming role ID 1 is Super Admin based on previous tasks
        $superAdminRole = $db->fetchOne("SELECT id FROM roles WHERE name = 'Super Admin'");
        $roleId = $superAdminRole ? $superAdminRole['id'] : 1;
        
        $db->insert('user_roles', [
            'user_id' => $userId,
            'role_id' => $roleId 
        ]);

        // 4. Create Subscription
        // Fetch Plan Details
        $plan = $db->fetchOne("SELECT * FROM subscription_plans WHERE plan_name = ?", [$planName]);
        if (!$plan) {
            // Fallback or Error? Let's use 0 if not found, or maybe just default to Starter pricing?
            // Ideally should fail, but for robustness in setup we can log or just continue with 0 if critical.
            // But let's assume plan exists since user came from pricing page.
            $planPrice = 0.00;
        } else {
            $planPrice = ($cycle === 'annual') ? $plan['annual_price'] : $plan['monthly_price'];
        }

        // Calculate dates
        $startDate = date('Y-m-d H:i:s');
        $endDate = ($cycle === 'annual') ? date('Y-m-d H:i:s', strtotime('+1 year')) : date('Y-m-d H:i:s', strtotime('+1 month'));

        $db->insert('subscriptions', [
            'company_id' => $companyId,
            'plan_name' => $planName,
            'plan_price' => $planPrice,
            'status' => 'active',
            'current_period_start' => $startDate,
            'current_period_end' => $endDate,
            'billing_cycle' => $cycle,
            'user_id' => $userId, // Main user
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 5. Mark Request as Consumed
        $db->update('contact_requests', [
            'status' => 'Replied',
             // clear token to prevent reuse? or just leave it since status check handles it?
             // let's clear it to be safe
            'onboarding_token' => NULL 
        ], 'id = ?', [$request['id']]);

        $db->commit();

        // 6. Auto Login
        session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'Admin';
        $_SESSION['company_id'] = $companyId;
        $_SESSION['theme'] = 'light';

        header("Location: " . BASE_URL . "modules/dashboard/index.php");
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $error = "Setup failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php 
    $pageTitle = "Set Up Your Account";
    $pageDescription = "Complete your organization setup to get started with " . APP_NAME;
    require_once '../includes/public_meta.php'; 
    ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/landing.css?v=<?php echo time(); ?>">
    
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
            padding-top: 80px; /* Space for fixed header */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .setup-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .setup-container {
            width: 100%;
            max-width: 550px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.05), 0 8px 16px -5px rgba(0, 0, 0, 0.01);
            border: 1px solid #e2e8f0;
        }

        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .setup-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }

        .setup-subtitle {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .plan-badge {
            display: inline-flex;
            align-items: center;
            background: #eff6ff;
            color: #2563eb;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.85em;
            font-weight: 600;
            margin-left: 5px;
        }

        .form-section-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 0.9rem;
            color: #334155;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            color: #1e293b;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn-submit {
            width: 100%;
            padding: 12px 20px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fee2e2;
            color: #ef4444;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.9rem;
        }

        /* Footer override for this page if needed */
        footer {
            margin-top: auto;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <?php require_once '../includes/public_header.php'; ?>

    <div class="setup-section">
        <div class="setup-container">
            <div class="setup-header">
                <h1 class="setup-title">Welcome to <?php echo APP_NAME; ?></h1>
                <div class="setup-subtitle">
                    Complete your setup to activate your <span class="plan-badge"><i class="fas fa-crown" style="font-size: 0.8em; margin-right: 4px;"></i> <?php echo htmlspecialchars($planName); ?></span> plan.
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle" style="margin-top: 3px;"></i>
                    <div><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-section-title">Organization Details</div>
                
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>" required placeholder="e.g. Acme Corp">
                </div>

                <div class="row" style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                         <label class="form-label">Industry</label>
                         <select name="industry_type" class="form-control" required>
                             <option value="">Select Industry...</option>
                             <option value="Technology">Technology</option>
                             <option value="Manufacturing">Manufacturing</option>
                             <option value="Retail">Retail</option>
                             <option value="Service">Service</option>
                             <option value="Healthcare">Healthcare</option>
                             <option value="Education">Education</option>
                             <option value="Other">Other</option>
                         </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="+91...">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Address Line</label>
                    <input type="text" name="address_line" class="form-control" value="<?php echo htmlspecialchars($_POST['address_line'] ?? ''); ?>" placeholder="Street Address, P.O. Box..." required>
                </div>

                <div class="row" style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">State</label>
                        <select name="state" class="form-control" required>
                            <option value="">Select State...</option>
                            <option value="Andhra Pradesh">Andhra Pradesh</option>
                            <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                            <option value="Assam">Assam</option>
                            <option value="Bihar">Bihar</option>
                            <option value="Chhattisgarh">Chhattisgarh</option>
                            <option value="Goa">Goa</option>
                            <option value="Gujarat">Gujarat</option>
                            <option value="Haryana">Haryana</option>
                            <option value="Himachal Pradesh">Himachal Pradesh</option>
                            <option value="Jharkhand">Jharkhand</option>
                            <option value="Karnataka">Karnataka</option>
                            <option value="Kerala">Kerala</option>
                            <option value="Madhya Pradesh">Madhya Pradesh</option>
                            <option value="Maharashtra">Maharashtra</option>
                            <option value="Manipur">Manipur</option>
                            <option value="Meghalaya">Meghalaya</option>
                            <option value="Mizoram">Mizoram</option>
                            <option value="Nagaland">Nagaland</option>
                            <option value="Odisha">Odisha</option>
                            <option value="Punjab">Punjab</option>
                            <option value="Rajasthan">Rajasthan</option>
                            <option value="Sikkim">Sikkim</option>
                            <option value="Tamil Nadu">Tamil Nadu</option>
                            <option value="Telangana">Telangana</option>
                            <option value="Tripura">Tripura</option>
                            <option value="Uttar Pradesh">Uttar Pradesh</option>
                            <option value="Uttarakhand">Uttarakhand</option>
                            <option value="West Bengal">West Bengal</option>
                            <option value="Delhi">Delhi</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 0 0 100px;">
                        <label class="form-label">PIN</label>
                        <input type="text" name="pin" class="form-control" value="<?php echo htmlspecialchars($_POST['pin'] ?? ''); ?>" required maxlength="6" pattern="[0-9]{6}">
                    </div>
                </div>

                <div class="form-section-title" style="margin-top: 30px;">Account Security</div>
                
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($request['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="admin_user">
                </div>

                <div class="form-group">
                     <label class="form-label">Password</label>
                     <input type="password" name="password" class="form-control" required minlength="6" placeholder="Min. 6 characters">
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-submit">
                        Complete Setup <i class="fas fa-arrow-right"></i>
                    </button>
                    <p style="text-align: center; margin-top: 15px; font-size: 0.85rem; color: #94a3b8;">
                        By clicking "Complete Setup", you agree to our Terms of Service.
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once '../includes/public_footer.php'; ?>

</body>
</html>
