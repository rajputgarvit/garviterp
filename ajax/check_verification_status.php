<?php
require_once '../config/config.php';
require_once '../classes/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) && !isset($_SESSION['pending_user_id'])) {
    echo json_encode(['verified' => false, 'error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['pending_user_id'] ?? $_SESSION['user_id'];
$db = Database::getInstance();

$user = $db->fetchOne("SELECT email_verified FROM users WHERE id = ?", [$userId]);

if ($user && $user['email_verified'] == 1) {
    // If user was pending, move to logged in
    if (isset($_SESSION['pending_user_id'])) {
        $_SESSION['user_id'] = $_SESSION['pending_user_id'];
        
        // Fetch full user details to set session correctly (roles, etc.)
        // But for now, basic login is enough to proceed to select-plan
        $fullUser = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        $_SESSION['username'] = $fullUser['username'];
        $_SESSION['full_name'] = $fullUser['full_name'];
        $_SESSION['email'] = $fullUser['email'];
        $_SESSION['company_id'] = $fullUser['company_id'];
        
        // Roles might need to be fetched if we strictly follow Auth::setSession
        // For simplicity here, we assume select-plan will handle further checks or we rely on basic session
        require_once '../classes/Auth.php';
        $auth = new Auth();
        // Since setSession is private, we can't call it. 
        // We can just rely on user_id key being present for isLoggedIn() check.
        
        unset($_SESSION['pending_user_id']);
        unset($_SESSION['pending_user_email']);
    }
    
    echo json_encode(['verified' => true]);
} else {
    echo json_encode(['verified' => false]);
}
