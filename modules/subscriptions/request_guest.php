<?php
require_once '../../config/config.php';
require_once '../../classes/Database.php';

// Set JSON header
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$company = trim($_POST['company_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$plan = trim($_POST['plan_name'] ?? '');
$cycle = trim($_POST['billing_cycle'] ?? '');

if (empty($name) || empty($email) || empty($plan)) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing.']);
    exit;
}

try {
    $db = Database::getInstance();
    
    $subject = "New Plan Request: $plan ($cycle)";
    $message = "
    Plan Request Details:
    ---------------------
    Plan: $plan
    Billing Cycle: $cycle
    
    Contact Information:
    --------------------
    Name: $name
    Email: $email
    Company: $company
    Phone: $phone
    
    This user requested a plan via the pricing page without being logged in.
    ";

    $db->insert('contact_requests', [
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'status' => 'New',
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // Send Emails
    require_once '../../classes/Mail.php';
    $mail = new Mail();

    // 1. Notify Super Admin
    $adminEmail = 'garvit.rajput@acculynce.com'; // Default fallback


    $adminSubject = "New Guest Plan Request: $company";
    $adminBody = "
        <h3>New Guest Plan Request</h3>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Company:</strong> $company</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Requested Plan:</strong> $plan</p>
        <p><strong>Billing Cycle:</strong> $cycle</p>
        <p>This request is logged in 'Inquiries'. Please review and create their account manually.</p>
    ";
    $mail->sendWithResend($adminEmail, $adminSubject, $adminBody);

    // 2. Notify Guest User
    $userSubject = "We received your Request - $plan Plan";
    $userBody = "
        <p>Hello $name,</p>
        <p>Thank you for your interest in the <strong>$plan</strong> plan at " . APP_NAME . ".</p>
        <p>We have successfully received your request.</p>
        <p>Our team is reviewing your details. <strong>We will set up your account and share your login credentials shortly via a confirmation email.</strong></p>
        <p>If you have any immediate questions, simply reply to this email.</p>
        <br>
        <p>Best Regards,<br>" . APP_NAME . " Team</p>
    ";
    $mail->sendWithResend($email, $userSubject, $userBody);

    echo json_encode(['success' => true, 'message' => 'Your request has been received. We will contact you shortly.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
