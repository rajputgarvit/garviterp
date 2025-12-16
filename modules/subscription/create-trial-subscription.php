<?php
// session_start(); // Handled in config.php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Subscription.php';

$auth = new Auth();
$subscription = new Subscription();

// Check if user is coming from checkout
if (!isset($_SESSION['selected_plan'])) {
    header('Location: ../auth/register.php');
    exit;
}

$userId = $_SESSION['pending_user_id'] ?? $_SESSION['user_id'] ?? null;

if (!$userId) {
    header('Location: ../auth/register.php');
    exit;
}

$planName = $_SESSION['selected_plan'];
$billingCycle = $_SESSION['selected_billing'] ?? 'monthly';

try {
    // Create trial subscription
    $subscriptionId = $subscription->createSubscription($userId, $planName, $billingCycle);
    
    // Clear session variables
    unset($_SESSION['selected_plan']);
    unset($_SESSION['selected_billing']);
    
    // If it was a pending user (registration flow), log them in
    if (isset($_SESSION['pending_user_id'])) {
        // Initialize DB connection here if not already done
        $db = Database::getInstance();

        unset($_SESSION['pending_user_id']);
        unset($_SESSION['pending_user_email']); // Also clear pending email if it exists
        
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        if ($user) { // Ensure user is found before setting session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['company_id'] = $user['company_id'];
            
            // Fetch roles
            $roles = $db->fetchAll("SELECT r.name FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ?", [$userId]);
            $_SESSION['roles'] = array_column($roles, 'name');
        }
        
        // Redirect to onboarding
        header('Location: ../auth/onboarding.php');
    } else {
        // Existing user upgrade
        header('Location: ../dashboard/index.php?success=Plan upgraded successfully.');
    }
    // Ideally to a "Welcome" or "Success" page, then dashboard
    header('Location: ../dashboard/index.php?success=Welcome! Your 14-day free trial has started.');
    exit;

} catch (Exception $e) {
    // Handle error
    echo "Error creating subscription: " . $e->getMessage();
    exit;
}
?>
