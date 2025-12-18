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
            filter: brightness(0) invert(1); /* Make logo white for dark bg */
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
            max-width: 420px;
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

        /* Mobile Responsive */
        @media (max-width: 900px) {
            body {
                flex-direction: column;
                overflow-y: auto;
            }
            .split-left {
                display: none; /* Hide branding on mobile for cleaner look or make smaller */
            }
            .split-right {
                padding: 20px;
                min-height: 100vh;
            }
             /* Or show small header */
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
            <h1 class="brand-heading">The Operating System for Modern Business</h1>
            <p class="brand-text">Unify your entire organization on one platform. Manage inventory, finance, and people with enterprise-grade precision.</p>
        </div>
    </div>

    <!-- Right Side -->
    <div class="split-right">
        <div class="auth-wrapper">
            <!-- Mobile Logo (visible only on small screens) -->
            <div class="mobile-logo">
                <img src="<?php echo BASE_URL; ?>public/assets/images/logo.svg" alt="Acculynce">
            </div>

            <div class="auth-header">
                <h1 class="auth-title">Welcome back</h1>
                <p class="auth-subtitle">Please sign in to your dashboard</p>
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
                        <a href="#" class="auth-link" style="font-size: 13px;">Forgot password?</a>
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
            
            <div style="margin-top: 40px; text-align: center; color: #cbd5e1; font-size: 12px;">
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
