<?php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';

$auth = new Auth();
$email = $_SESSION['pending_user_email'] ?? '';

// If verified, redirect
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    // Re-fetch to check verified status if needed, but for now just check logic
    // We'll rely on verify-email.php to update session or re-login
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - <?php echo APP_NAME; ?></title>
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
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 480px;
            width: 90%;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: #eff6ff;
            color: #2563eb;
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
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn:hover { background: #1d4ed8; }
        .resend-link {
            display: block;
            margin-top: 20px;
            color: #64748b;
            font-size: 14px;
            text-decoration: none;
        }
        .resend-link:hover { color: #2563eb; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-circle">
            <i class="fas fa-envelope-open-text"></i>
        </div>
        <h1>Check your email</h1>
        <p>We've sent a verification link to <strong><?php echo htmlspecialchars($email); ?></strong>.<br>
        Please click the link to verify your account and continue.</p>
        
        <div style="font-size: 14px; color: #94a3b8; margin-bottom: 20px;">
            Can't find it? Check your spam folder.
        </div>

        <a href="login.php" class="resend-link">Back to Login</a>
    </div>

    <script>
        function checkVerification() {
            fetch('../../ajax/check_verification_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.verified) {
                        window.location.href = '../subscription/select-plan.php';
                    }
                })
                .catch(err => console.error('Verification check failed', err));
        }

        // Check every 3 seconds
        setInterval(checkVerification, 3000);
    </script>
</body>
</html>
