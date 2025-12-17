<?php
require_once '../../config/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';

$auth = new Auth();
$db = Database::getInstance();

// 1. Verify Super Admin Status
if (!$auth->hasRole('Super Admin')) {
    die("Access Denied: Super Admin privileges required.");
}

$targetCompanyId = $_GET['company_id'] ?? null;

if (!$targetCompanyId) {
    die("Invalid Company ID specified.");
}

// 2. Fetch Company Owner
// We prioritize the primary owner (creator) or any admin of that company
$targetUser = $db->fetchOne(
    "SELECT u.*, r.name as role_name 
     FROM users u 
     JOIN user_roles ur ON u.id = ur.user_id 
     JOIN roles r ON ur.role_id = r.id
     WHERE u.company_id = ? 
     ORDER BY u.created_at ASC 
     LIMIT 1",
    [$targetCompanyId]
);

if (!$targetUser) {
    die("No eligible user found for this company.");
}

// 3. Store Original Session (if not already stored)
if (!isset($_SESSION['original_user'])) {
    $_SESSION['original_user'] = [
        'id' => $_SESSION['user_id'],
        'role' => $_SESSION['role'],
        'full_name' => $_SESSION['full_name'],
        'email' => $_SESSION['email']
    ];
}

// 4. Set Impersonation Session
$_SESSION['user_id'] = $targetUser['id'];
$_SESSION['role'] = $targetUser['role_name'];
$_SESSION['full_name'] = $targetUser['full_name'];
$_SESSION['email'] = $targetUser['email'];
$_SESSION['company_id'] = $targetUser['company_id'];
$_SESSION['is_impersonating'] = true;

// 5. Log the Action (Optional but recommended)
// error_log("Super Admin {$_SESSION['original_user']['id']} impersonated user {$targetUser['id']}");

// 6. Redirect to Dashboard
header('Location: ' . MODULES_URL . '/dashboard.php');
exit;
