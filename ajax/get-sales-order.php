<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Database.php';

header('Content-Type: application/json');

$auth = new Auth();
$user = $auth->getCurrentUser();

if (!$user) {
    echo JSON_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

$orderId = intval($_GET['id']);
$db = Database::getInstance();

try {
    // Fetch Order Details
    $order = $db->fetchOne(
        "SELECT * FROM sales_orders WHERE id = ? AND company_id = ?", 
        [$orderId, $user['company_id']]
    );

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Fetch Order Items
    $items = $db->fetchAll(
        "SELECT i.*, p.name as product_name, p.product_code, p.selling_price as current_price, 
         p.has_serial_number, p.has_warranty, p.has_expiry_date
         FROM sales_order_items i 
         JOIN products p ON i.product_id = p.id 
         WHERE i.order_id = ? AND i.company_id = ?",
        [$orderId, $user['company_id']]
    );

    // Fetch Customer Details
    $customer = $db->fetchOne(
        "SELECT * FROM customers WHERE id = ? AND company_id = ?",
        [$order['customer_id'], $user['company_id']]
    );

    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items,
        'customer' => $customer
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
