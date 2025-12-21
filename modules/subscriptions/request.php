<?php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/Subscription.php';

// Set JSON header
header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = $auth->getCurrentUser();
$companyId = $user['company_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $planName = $_POST['plan_name'] ?? '';
    $billingCycle = $_POST['billing_cycle'] ?? 'monthly';

    if (empty($planName)) {
        throw new Exception("Plan name is required");
    }

    $sub = new Subscription($companyId);
    $sub->requestSubscription($companyId, $user['id'], $planName, $billingCycle);

    // Send Emails
    require_once '../../classes/Mail.php';
    $mail = new Mail();
    $db = Database::getInstance();

    // 1. Notify Super Admin
    $adminEmail = 'garvit.rajput@acculynce.com'; // Default fallback

    $adminSubject = "New Subscription Request: {$user['company_name']}";
    $adminBody = "
        <h3>New Plan Request Received</h3>
        <p><strong>Company:</strong> {$user['company_name']}</p>
        <p><strong>User:</strong> {$user['username']} ({$user['email']})</p>
        <p><strong>Requested Plan:</strong> {$planName}</p>
        <p><strong>Billing Cycle:</strong> {$billingCycle}</p>
        <p>Please review explicitly in the Admin Panel.</p>
    ";
    $mail->sendWithResend($adminEmail, $adminSubject, $adminBody);

    // 2. Notify User
    $userSubject = "We received your Plan Request";
    $userBody = "
        <p>Hello {$user['username']},</p>
        <p>Thank you for requesting the <strong>{$planName}</strong> plan.</p>
        <p>Our team has received your request and will review it shortly. Once approved, your subscription will be updated automatically.</p>
        <p>If you have any questions, please reply to this email.</p>
        <br>
        <p>Best Regards,<br>" . APP_NAME . " Team</p>
    ";
    $mail->sendWithResend($user['email'], $userSubject, $userBody);

    echo json_encode(['success' => true, 'message' => 'Subscription request submitted successfully.']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
