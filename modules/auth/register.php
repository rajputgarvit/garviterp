<?php
// session_start(); // Handled in config.php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Database.php';

$auth = new Auth();
$db = Database::getInstance();

$error = '';
$success = '';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: ../dashboard/index.php');
    exit;
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $companyName = trim($_POST['company_name']);
        $fullName = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        // Validation
        if (empty($companyName) || empty($fullName) || empty($email) || empty($password)) {
            throw new Exception("All fields are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }

        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match");
        }

        // Check if email exists
        $existing = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            throw new Exception("Email already registered");
        }

        // Create username from email
        $username = explode('@', $email)[0] . '_' . time();

        // Register user
        $result = $auth->register([
            'username' => $username,
            'email' => $email,
            'full_name' => $fullName,
            'password' => $password,
            'company_name' => $companyName,
            'is_active' => 1
        ]);

        if ($result['success']) {
            // Send verification email
            $verification = $auth->sendVerificationEmail($result['user_id'], $email);
            
            // Store user ID in session for plan selection
            $_SESSION['pending_user_id'] = $result['user_id'];
            $_SESSION['pending_user_email'] = $email;
            
            // Redirect to verification pending
            header('Location: verification-pending.php');
            exit;
        } else {
            $error = $result['message'];
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --text-dark: #1e293b;
            --text-gray: #64748b;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --error-color: #ef4444;
            --success-color: #10b981;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            height: 100vh;
            overflow: hidden;
            display: flex;
        }

        /* Left Side - Branding */
        .split-left {
            flex: 1;
            background: #0f172a;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            color: white;
            overflow: hidden;
        }

        .split-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.2) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.1) 0%, transparent 40%);
            z-index: 1;
        }

        .brand-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 480px;
        }

        .brand-logo {
            max-width: 220px;
            height: auto;
            margin-bottom: 40px;
            filter: brightness(0) invert(1);
        }

        .brand-heading {
            font-size: 42px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 24px;
            letter-spacing: -0.02em;
        }

        .brand-text {
            font-size: 18px;
            color: #94a3b8;
            line-height: 1.6;
        }

        /* Right Side - Form */
        .split-right {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            overflow-y: auto;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 460px; /* Slightly wider for register form */
            padding: 20px;
        }

        .auth-header {
            margin-bottom: 32px;
        }

        .auth-title {
            font-size: 30px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .auth-subtitle {
            color: var(--text-gray);
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            color: var(--text-dark);
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .auth-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }

        .auth-link:hover {
            text-decoration: underline;
        }

        .auth-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 14px;
            color: var(--text-gray);
        }

        .divider {
            margin: 24px 0;
            position: relative;
            text-align: center;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e2e8f0;
        }
        
        .divider span {
            background: white;
            padding: 0 10px;
            color: #94a3b8;
            font-size: 12px;
            position: relative;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fee2e2;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #dcfce7;
        }

        .password-strength {
            margin-top: 6px;
            font-size: 12px;
            height: 18px;
        }
        
        .strength-weak { color: #ef4444; }
        .strength-medium { color: #f59e0b; }
        .strength-strong { color: #10b981; }

        /* Mobile Responsive */
        @media (max-width: 900px) {
            body {
                flex-direction: column;
                overflow-y: auto;
            }
            .split-left {
                display: none;
            }
            .split-right {
                padding: 20px;
                min-height: 100vh;
            }
             .mobile-logo {
                 display: block;
                 text-align: center;
                 margin-bottom: 30px;
             }
             .mobile-logo img {
                 max-height: 40px;
             }
        }
        @media (min-width: 901px) {
            .mobile-logo { display: none; }
        }
    </style>
</head>
<body>
    <!-- Left Side -->
    <div class="split-left">
        <div class="brand-content">
            <img src="<?php echo BASE_URL; ?>public/assets/images/logo.svg" alt="Acculynce Logo" class="brand-logo">
            <h1 class="brand-heading">Start your 14-day free trial</h1>
            <p class="brand-text">Join thousands of growing businesses managing their operations on Acculynce.</p>
        </div>
    </div>

    <!-- Right Side -->
    <div class="split-right">
        <div class="auth-wrapper">
             <div class="mobile-logo">
                <img src="<?php echo BASE_URL; ?>public/assets/images/logo.svg" alt="Acculynce">
            </div>

            <div class="auth-header">
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Get started with your free account today</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label class="form-label">Company Name <span style="color: var(--error-color)">*</span></label>
                    <input type="text" name="company_name" class="form-control" required 
                           value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>" placeholder="Your Company Name">
                </div>

                <div class="form-group">
                    <label class="form-label">Your Full Name <span style="color: var(--error-color)">*</span></label>
                    <input type="text" name="full_name" class="form-control" required
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" placeholder="John Doe">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address <span style="color: var(--error-color)">*</span></label>
                    <input type="email" name="email" class="form-control" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="name@company.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="+91 98765 43210">
                </div>

                <div class="form-group">
                    <label class="form-label">Password <span style="color: var(--error-color)">*</span></label>
                    <input type="password" name="password" class="form-control" required 
                           id="password" minlength="8" placeholder="••••••••">
                    <div class="password-strength" id="passwordStrength"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password <span style="color: var(--error-color)">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" required 
                           id="confirmPassword" minlength="8" placeholder="••••••••">
                </div>

                <button type="submit" class="btn-primary">
                    Create Account & Choose Plan
                </button>
            </form>

            <div class="divider">
                <span>OR</span>
            </div>

            <div class="auth-footer">
                Already have an account? <a href="login.php" class="auth-link">Sign in</a>
            </div>
            
             <div style="margin-top: 40px; text-align: center; color: #cbd5e1; font-size: 12px;">
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
            </div>
        </div>
    </div>

    <script>
        // Password strength checker
        const password = document.getElementById('password');
        const strengthDiv = document.getElementById('passwordStrength');

        password.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;
            
            if (value.length >= 8) strength++;
            if (value.match(/[a-z]/) && value.match(/[A-Z]/)) strength++;
            if (value.match(/[0-9]/)) strength++;
            if (value.match(/[^a-zA-Z0-9]/)) strength++;

            if (strength === 0) {
                strengthDiv.textContent = '';
            } else if (strength <= 2) {
                strengthDiv.textContent = 'Weak password';
                strengthDiv.className = 'password-strength strength-weak';
            } else if (strength === 3) {
                strengthDiv.textContent = 'Medium password';
                strengthDiv.className = 'password-strength strength-medium';
            } else {
                strengthDiv.textContent = 'Strong password';
                strengthDiv.className = 'password-strength strength-strong';
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters!');
                return false;
            }
        });
    </script>
</body>
</html>
