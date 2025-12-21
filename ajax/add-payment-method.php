<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';
require_once '../classes/Database.php';

header('Content-Type: application/json');

$auth = new Auth();
$user = $auth->getCurrentUser();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$methodName = trim($_POST['method_name'] ?? '');

if (empty($methodName)) {
    echo json_encode(['success' => false, 'message' => 'Payment method name is required']);
    exit;
}

$db = Database::getInstance();

try {
    // Check if exists
    $existing = $db->fetchOne("SELECT id FROM payment_methods WHERE method_name = ?", [$methodName]);
    
    if ($existing) {
        echo json_encode(['success' => true, 'message' => 'Payment method already exists']);
        exit;
    }

    // Get max display order
    $maxOrder = $db->fetchOne("SELECT MAX(display_order) as max_order FROM payment_methods");
    $nextOrder = ($maxOrder['max_order'] ?? 0) + 1;

    $db->insert('payment_methods', [
        'method_name' => $methodName,
        'display_order' => $nextOrder,
        'is_active' => 1
    ]);

    echo json_encode(['success' => true, 'message' => 'Payment method added successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error adding payment method: ' . $e->getMessage()]);
}
