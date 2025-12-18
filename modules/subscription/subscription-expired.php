<?php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired - <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Include Landing CSS -->
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

        .expired-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            text-align: center;
            max-width: 500px;
            width: 100%;
            border: 1px solid var(--border-color);
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 32px;
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);
        }

        .expired-card h1 {
            color: var(--text-primary);
            font-size: 1.8rem;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .expired-card p {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 1.05rem;
        }

        .btn-upgrade {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: var(--primary-color);
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            box-sizing: border-box;
            border: none;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }

        .btn-upgrade:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(79, 70, 229, 0.3);
            color: white;
        }

        .logout-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--text-light);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .logout-link:hover {
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php require_once '../../includes/public_header.php'; ?>

    <div class="main-content">
        <div class="expired-card">
            <div class="icon-circle">
                <i class="fas fa-lock"></i>
            </div>
            <h1>Subscription Expired</h1>
            <p>Your subscription or free trial has ended. To continue accessing the dashboard and features, please upgrade your plan.</p>
            
            <a href="../subscription/checkout.php?upgrade=1" class="btn-upgrade">
                <i class="fas fa-rocket"></i> Upgrade Plan
            </a>
            <br>
            <a href="../auth/logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once '../../includes/public_footer.php'; ?>
</body>
</html>
