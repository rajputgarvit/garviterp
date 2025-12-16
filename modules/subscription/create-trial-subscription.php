<?php
// session_start(); // Handled in config.php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Subscription.php';

$subscription = new Subscription();

// Check for user ID (either pending or logged in)
$userId = $_SESSION['pending_user_id'] ?? $_SESSION['user_id'] ?? null;

if (!$userId) {
    header('Location: select-plan.php');
    exit;
}

// Initialize DB connection to get company_id
$db = Database::getInstance();
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

if (!$user) {
    // User not found, redirect to registration or error page
    header('Location: select-plan.php');
    exit;
}

$companyId = $user['company_id'];

$planName = $_GET['plan'] ?? '';
$billingCycle = $_GET['billing'] ?? 'monthly';

// Validate plan and billing cycle if necessary
if (empty($planName)) {
    header('Location: select-plan.php?error=No plan selected.');
    exit;
}

try {
    // Create new trial subscription for COMPANY
    $subscriptionId = $subscription->createSubscription($companyId, $planName, $billingCycle, 'trial', $userId);
    
    // Clear session variables if they were used for selection
    unset($_SESSION['selected_plan']);
    unset($_SESSION['selected_billing']);
    
    // If it was a pending user (registration flow), log them in
    if (isset($_SESSION['pending_user_id'])) {

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
