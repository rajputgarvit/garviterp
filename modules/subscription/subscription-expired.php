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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
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
        }
        h1 {
            color: #1e293b;
            font-size: 24px;
            margin-bottom: 12px;
        }
        p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
            width: 100%;
            box-sizing: border-box;
        }
        .btn:hover { background: #1d4ed8; }
        .logout-link {
            display: inline-block;
            margin-top: 20px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
        }
        .logout-link:hover { color: #333; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-circle">
            <i class="fas fa-lock"></i>
        </div>
        <h1>Subscription Expired</h1>
        <p>Your subscription or free trial has ended. To continue accessing the dashboard and features, please upgrade your plan.</p>
        
        <a href="../subscription/checkout.php?upgrade=1" class="btn">Upgrade Plan</a>
        <br>
        <a href="../auth/logout.php" class="logout-link">Logout</a>
    </div>
</body>
</html>
