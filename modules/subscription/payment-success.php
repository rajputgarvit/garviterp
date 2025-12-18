<?php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';

// Logic to check subscription status could go here if needed
$subscriptionId = $_GET['subscription_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Acculynce</title>
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
        .success-card {
            background: white;
            padding: 50px;
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            text-align: center;
            max-width: 500px;
            width: 100%;
            border: 1px solid var(--border-color);
            animation: fadeIn 0.5s ease-out;
        }
        .icon-circle {
            width: 90px;
            height: 90px;
            background: #dcfce7;
            color: #16a34a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
            box-shadow: 0 4px 10px rgba(22, 163, 74, 0.2);
            animation: bounceIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        h1 {
            color: var(--text-primary);
            margin-bottom: 15px;
            font-weight: 800;
        }
        p {
            color: var(--text-secondary);
            margin-bottom: 40px;
            font-size: 1.1rem;
        }
        .btn-dashboard {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: var(--primary-color);
            color: white;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);
        }
        .btn-dashboard:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(79, 70, 229, 0.4);
            color: white;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes bounceIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php require_once '../../includes/public_header.php'; ?>

    <div class="main-content">
        <div class="success-card">
            <div class="icon-circle">
                <i class="fas fa-check"></i>
            </div>
            <h1>Payment Successful!</h1>
            <p>Thank you for subscribing. Your account has been upgraded and you now have full access to all features.</p>
            
            <a href="../dashboard/index.php" class="btn-dashboard">
                Go to Dashboard <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once '../../includes/public_footer.php'; ?>
</body>
</html>
