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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/landing.css">
    <style>
        body {
            background-color: var(--bg-light);
            padding-top: 80px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .auth-container {
            max-width: 1200px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        /* Left Side Content */
        .auth-content {
            padding-right: 40px;
            animation: fadeInLeft 0.6s ease-out;
        }

        .auth-content h1 {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 24px;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }

        .auth-content h1 span {
            background: linear-gradient(135deg, #4f46e5 0%, #0ea5e9 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .auth-content p {
            font-size: 1.15rem;
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 40px;
        }

        .feature-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 16px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .feature-icon {
            width: 32px;
            height: 32px;
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .auth-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 500px;
            border: 1px solid var(--border-color);
            margin-left: auto;
            animation: fadeInRight 0.6s ease-out;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .auth-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .step-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--border-color);
            transition: all 0.3s ease;
        }

        .step-dot.active {
            background: var(--primary-color);
            transform: scale(1.2);
        }

        .form-section {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .form-section.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--text-primary);
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 4px var(--accent-glow);
        }

        .btn-primary {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-secondary {
            width: 100%;
            padding: 14px;
            background: #f1f5f9;
            color: var(--text-secondary);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 12px;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            color: var(--text-primary);
        }

        .auth-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .auth-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .auth-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .buttons-row {
            display: flex;
            gap: 12px;
        }

        .password-strength {
            margin-top: 6px;
            font-size: 0.8rem;
            height: 18px;
            font-weight: 500;
        }
        
        .strength-weak { color: #ef4444; }
        .strength-medium { color: #f59e0b; }
        .strength-strong { color: #10b981; }

        .error-msg {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 4px;
            display: none;
        }

        @media (max-width: 968px) {
            .auth-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            .auth-content {
                padding-right: 0;
                text-align: center;
            }
            .auth-content h1 {
                font-size: 2.5rem;
            }
            .feature-list {
                align-items: center;
            }
            .auth-card {
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php require_once '../../includes/public_header.php'; ?>

    <div class="main-content">
        <div class="auth-container">
            <!-- Left Side -->
            <div class="auth-content">
                <h1>Join the Future of <span>Business</span></h1>
                <p>Start your 14-day free trial today. No credit card required. Experience the power of unified business management.</p>
                
                <ul class="feature-list">
                    <li class="feature-item">
                        <div class="feature-icon"><i class="fas fa-rocket"></i></div>
                        <div>Setup in less than 5 minutes</div>
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                        <div>Bank-grade data security</div>
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon"><i class="fas fa-headset"></i></div>
                        <div>24/7 Priority Support</div>
                    </li>
                </ul>
            </div>

            <!-- Right Side -->
            <div class="auth-card">
                <div class="auth-header">
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Get started with your free 14-day trial</p>
            </div>

            <div class="step-indicator">
                <div class="step-dot active" id="dot1"></div>
                <div class="step-dot" id="dot2"></div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" style="background: #fef2f2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fee2e2;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
                <!-- Step 1: Personal Info -->
                <div class="form-section active" id="step1">
                    <div class="form-group">
                        <label class="form-label">Full Name <span style="color: #ef4444">*</span></label>
                        <input type="text" name="full_name" id="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" placeholder="John Doe">
                        <div class="error-msg" id="err-name">Full name is required</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address <span style="color: #ef4444">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="name@company.com">
                        <div class="error-msg" id="err-email">Valid email is required</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="+91 98765 43210">
                    </div>

                    <button type="button" class="btn-primary" onclick="nextStep()">
                        Next Step <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
                    </button>
                </div>

                <!-- Step 2: Company & Security -->
                <div class="form-section" id="step2">
                    <div class="form-group">
                        <label class="form-label">Company Name <span style="color: #ef4444">*</span></label>
                        <input type="text" name="company_name" id="company_name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>" placeholder="Your Company Name">
                        <div class="error-msg" id="err-company">Company name is required</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password <span style="color: #ef4444">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" 
                               placeholder="Min. 8 characters">
                        <div class="password-strength" id="passwordStrength"></div>
                        <div class="error-msg" id="err-password">Password must be at least 8 characters</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password <span style="color: #ef4444">*</span></label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                               placeholder="Retype password">
                        <div class="error-msg" id="err-confirm">Passwords do not match</div>
                    </div>

                    <div class="buttons-row">
                        <button type="button" class="btn-secondary" onclick="prevStep()" style="width: 40%; margin-top: 0;">Back</button>
                        <button type="submit" class="btn-primary" style="width: 60%; margin-top: 0;">Create Account</button>
                    </div>
                </div>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php" class="auth-link">Sign in</a>
            </div>
        </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once '../../includes/public_footer.php'; ?>

    <script>
        // Use PHP's posted values to possibly open step 2 if errors occurred there? 
        // For simplicity, always start at step 1 unless we want complex logic. 
        // Standard user flow is linear.

        function nextStep() {
            // Validate Step 1
            const name = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            let valid = true;

            if (!name) {
                document.getElementById('err-name').style.display = 'block';
                valid = false;
            } else {
                document.getElementById('err-name').style.display = 'none';
            }

            if (!email || !emailRegex.test(email)) {
                document.getElementById('err-email').style.display = 'block';
                valid = false;
            } else {
                document.getElementById('err-email').style.display = 'none';
            }

            if (valid) {
                document.getElementById('step1').classList.remove('active');
                document.getElementById('step2').classList.add('active');
                document.getElementById('dot1').classList.remove('active');
                document.getElementById('dot2').classList.add('active');
            }
        }

        function prevStep() {
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step1').classList.add('active');
            document.getElementById('dot2').classList.remove('active');
            document.getElementById('dot1').classList.add('active');
        }

        // Final Validation on Submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const company = document.getElementById('company_name').value.trim();
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;

            let valid = true;

            if (!company) {
                document.getElementById('err-company').style.display = 'block';
                valid = false;
            } else {
                document.getElementById('err-company').style.display = 'none';
            }

            if (password.length < 8) {
                document.getElementById('err-password').style.display = 'block';
                valid = false;
            } else {
                document.getElementById('err-password').style.display = 'none';
            }

            if (password !== confirm) {
                document.getElementById('err-confirm').style.display = 'block';
                valid = false;
            } else {
                document.getElementById('err-confirm').style.display = 'none';
            }

            if (!valid) {
                e.preventDefault();
            }
        });

        // Password Strength
        const passwordInput = document.getElementById('password');
        const strengthDiv = document.getElementById('passwordStrength');

        passwordInput.addEventListener('input', function() {
            const value = this.value;
            if (!value) {
                strengthDiv.textContent = '';
                return;
            }
            let strength = 0;
            if (value.length >= 8) strength++;
            if (value.match(/[a-z]/) && value.match(/[A-Z]/)) strength++;
            if (value.match(/[0-9]/)) strength++;
            if (value.match(/[^a-zA-Z0-9]/)) strength++;

            if (strength <= 2) {
                strengthDiv.textContent = 'Weak';
                strengthDiv.className = 'password-strength strength-weak';
            } else if (strength === 3) {
                strengthDiv.textContent = 'Medium';
                strengthDiv.className = 'password-strength strength-medium';
            } else {
                strengthDiv.textContent = 'Strong';
                strengthDiv.className = 'password-strength strength-strong';
            }
        });
    </script>
</body>
</html>
