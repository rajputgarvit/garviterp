<?php
// session_start(); // Handled in config.php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';

$auth = new Auth();

    if ($auth->isLoggedIn()) {
        header('Location: ' . MODULES_URL . '/dashboard/index.php');
        exit;
    }

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($auth->login($username, $password)) {
            // Check for maintenance mode
            $db = Database::getInstance();
            $maintenanceMode = $db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode'")['setting_value'] ?? '0';
            
            if ($maintenanceMode == '1' && !$auth->hasRole('Super Admin')) {
                $auth->logout();
                $error = 'System is currently in maintenance mode. Only administrators can log in.';
            } else {
                // Check if Super Admin needs to setup company (e.g. after reset)
                if ($auth->hasRole('Super Admin')) {
                    $currentUser = $auth->getCurrentUser();
                    $companyId = $currentUser['company_id'];
                    $companyExists = false;
                    
                    if (!empty($companyId)) {
                        $companyCheck = $db->fetchOne("SELECT id FROM company_settings WHERE id = ?", [$companyId]);
                        if ($companyCheck) {
                            $companyExists = true;
                        }
                    }
                    
                    if (!$companyExists) {
                        header('Location: ' . MODULES_URL . '/admin/setup_company.php');
                        exit;
                    }
                }

                if ($auth->isAdmin()) {
                    header('Location: ' . MODULES_URL . '/admin/dashboard.php');
                } else {
                    header('Location: ' . MODULES_URL . '/dashboard/index.php');
                }
                exit;
            }
        } else {
            $error = 'Invalid username or password';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
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

        /* Right Side Card */
        .auth-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 440px;
            border: 1px solid var(--border-color);
            margin-left: auto; /* Push to right in grid */
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

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fee2e2;
        }

        .forgot-password {
            float: right;
            font-size: 0.85rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }

        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
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
                align-items: center; /* Center items on mobile */
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
            <!-- Left Side Content -->
            <div class="auth-content">
                <h1>Scale your business with <span>Confidence</span></h1>
                <p>The complete operating system for modern enterprises. Unify your team, streamline operations, and drive growth with Acculynce.</p>
                
                <ul class="feature-list">
                    <li class="feature-item">
                        <div class="feature-icon"><i class="fas fa-check"></i></div>
                        <div>Enterprise-grade Security</div>
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon"><i class="fas fa-check"></i></div>
                        <div>Real-time Analytics & Reporting</div>
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon"><i class="fas fa-check"></i></div>
                        <div>Seamless Team Collaboration</div>
                    </li>
                </ul>
            </div>

            <!-- Right Side Login Card -->
            <div class="auth-card">
                <div class="auth-header">
                <h1 class="auth-title">Welcome back</h1>
                <p class="auth-subtitle">Sign in to access your dashboard</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">Email or Username</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="name@company.com" required autofocus>
                </div>
                
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label class="form-label" for="password" style="margin-bottom: 0;">Password</label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> Sign in
                </button>
            </form>
            
            <div class="auth-footer">
                Don't have an account? <a href="register.php" class="auth-link">Create free account</a>
            </div>
        </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once '../../includes/public_footer.php'; ?>
</body>
</html>
