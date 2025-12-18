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
        .loader-container {
            margin-top: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: #64748b;
            font-size: 14px;
        }
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #e2e8f0;
            border-top: 2px solid #2563eb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
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

        <div class="loader-container">
            <div class="spinner"></div>
            <span>Checking verification status automatically...</span>
        </div>

        <div id="resend-container" style="margin-top: 20px; font-size: 14px; color: #64748b; min-height: 24px;">
            <span id="timer-text">Resend link in <span id="timer" style="font-weight: 600; color: #2563eb;">60</span>s</span>
            <a href="#" id="resend-btn" style="display: none; color: #2563eb; text-decoration: none; font-weight: 500;">Resend Verification Link</a>
            <span id="resend-message" style="display: none;"></span>
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

        // Resend Timer Logic
        let timeLeft = 60;
        const timerElement = document.getElementById('timer');
        const resendContainer = document.getElementById('resend-container');
        const timerText = document.getElementById('timer-text');
        const resendBtn = document.getElementById('resend-btn');
        const resendMessage = document.getElementById('resend-message');

        const countdown = setInterval(() => {
            timeLeft--;
            timerElement.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerText.style.display = 'none';
                resendBtn.style.display = 'inline-block';
            }
        }, 1000);

        resendBtn.addEventListener('click', function(e) {
            e.preventDefault();
            resendBtn.style.pointerEvents = 'none';
            resendBtn.textContent = 'Sending...';

            fetch('../../ajax/resend_verification.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                resendBtn.style.display = 'none';
                resendMessage.style.display = 'block';
                resendMessage.textContent = data.message;
                resendMessage.style.color = data.success ? '#10b981' : '#ef4444';
            })
            .catch(err => {
                resendBtn.textContent = 'Resend Link';
                resendBtn.style.pointerEvents = 'auto';
                alert('An error occurred. Please try again.');
            });
        });
    </script>
</body>
</html>
