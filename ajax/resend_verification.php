<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$userId = $_SESSION['pending_user_id'] ?? null;
$email = $_SESSION['pending_user_email'] ?? null;

if (!$userId || !$email) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please register again.']);
    exit;
}

try {
    $auth = new Auth();
    // Rate Limiting (60 seconds)
    $lastSent = $_SESSION['last_verification_sent_time'] ?? 0;
    $timeSinceLast = time() - $lastSent;
    
    if ($timeSinceLast < 60) {
        $waitSecs = 60 - $timeSinceLast;
        echo json_encode(['success' => false, 'message' => "Please wait $waitSecs seconds before resending."]);
        exit;
    }
    
    $sent = $auth->sendVerificationEmail($userId, $email);
    
    if ($sent) {
        $_SESSION['last_verification_sent_time'] = time();
        echo json_encode(['success' => true, 'message' => 'Verification email resent successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
