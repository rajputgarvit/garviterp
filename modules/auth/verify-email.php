<?php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';

$auth = new Auth();
$token = $_GET['token'] ?? '';
$success = false;
$message = '';

if ($token) {
    $result = $auth->verifyEmail($token);
    if ($result['success']) {
        $success = true;
        $userId = $result['user_id'];
        
        // Auto-login logic could go here if security allows, or force re-login.
        // For smoother UX, let's set session variables if we can identify the user securely.
        // However, `verifyEmail` just validates. Let's just update session if the user is already logged in with that ID (unlikely if strictly gated)
        // OR we can fetch user details and set session.
        
        // Fetch user to set session for seamless "Select Plan" flow
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if ($user) {
             // Set temporary session for plan selection flow
             $_SESSION['user_id'] = $user['id'];
             $_SESSION['username'] = $user['username'];
             $_SESSION['full_name'] = $user['full_name'];
             $_SESSION['email'] = $user['email'];
             $_SESSION['company_id'] = $user['company_id'];
             // Refresh roles logic if needed
        }

        // Redirect to plan selection
        header('Location: ../subscription/select-plan.php?verified=1');
        exit;
    } else {
        $message = $result['message'];
    }
} else {
    $message = "Invalid request.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification - <?php echo APP_NAME; ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f8fafc; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; }
        .error { color: #ef4444; margin-bottom: 20px; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="card">
        <?php if (!$success): ?>
            <h1 style="color: #ef4444;">Verification Failed</h1>
            <p class="error"><?php echo htmlspecialchars($message); ?></p>
            <a href="login.php" class="btn">Go to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>
