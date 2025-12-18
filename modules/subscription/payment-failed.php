<?php
require_once '../../config/config.php';
$error = $_GET['error'] ?? 'An unknown error occurred.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - Acculynce</title>
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
        .error-card {
            background: white;
            padding: 50px;
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            text-align: center;
            max-width: 500px;
            width: 100%;
            border: 1px solid var(--border-color);
        }
        .icon-circle {
            width: 90px;
            height: 90px;
            background: #fee2e2;
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
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
        .error-msg {
            background: #fef2f2;
            color: #b91c1c;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            word-break: break-all;
        }
        .btn-retry {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: var(--text-primary);
            color: white;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-retry:hover {
            background: black;
            transform: translateY(-2px);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php require_once '../../includes/public_header.php'; ?>

    <div class="main-content">
        <div class="error-card">
            <div class="icon-circle">
                <i class="fas fa-times"></i>
            </div>
            <h1>Payment Failed</h1>
            <p>We couldn't process your payment. Please try again or use a different payment method.</p>
            
            <?php if ($error): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <a href="javascript:history.back()" class="btn-retry">
                <i class="fas fa-redo"></i> Try Again
            </a>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once '../../includes/public_footer.php'; ?>
</body>
</html>
