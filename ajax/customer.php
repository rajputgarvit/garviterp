<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';
require_once '../classes/Database.php';
require_once '../classes/CodeGenerator.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance();
$user = $auth->getCurrentUser();

// --- GET Request: Fetch Customer Details ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id']) && !isset($_GET['customer_id'])) {
        echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
        exit;
    }

    $customerId = $_GET['id'] ?? $_GET['customer_id'];

    try {
        // Get customer details
        $customer = $db->fetchOne("
            SELECT * FROM customers 
            WHERE id = ? AND company_id = ?
        ", [$customerId, $user['company_id']]);

        if (!$customer) {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
            exit;
        }

        // Get default address
        $address = $db->fetchOne("
            SELECT * FROM customer_addresses 
            WHERE customer_id = ? AND is_default = 1
            LIMIT 1
        ", [$customerId]);
        
        // If no default address, try to get any address
        if (!$address) {
            $address = $db->fetchOne("
                SELECT * FROM customer_addresses 
                WHERE customer_id = ? 
                LIMIT 1
            ", [$customerId]);
        }

        echo json_encode([
            'success' => true,
            'customer' => $customer,
            'address' => $address
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// --- POST Request: Create/Update Customer ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Read JSON input if sent as raw body, otherwise use $_POST
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        // Basic Validation
        if (empty($input['customer_type'])) {
            throw new Exception('Customer Type is required');
        }
        
        // Conditional validation based on type
        if ($input['customer_type'] === 'Company' && empty($input['company_name'])) {
            throw new Exception('Company Name is required for Company type customers');
        }
        if ($input['customer_type'] === 'Individual' && empty($input['contact_person'])) {
             // For individual, contact_person essentially serves as the name if company_name is empty
             if (empty($input['company_name'])) {
                 $input['company_name'] = $input['contact_person']; // Fallback
             }
        }
        
        // Start Transaction
        $db->beginTransaction();
        
        $codeGen = new CodeGenerator();
        
        // Generate Code if not provided
        $customerCode = $input['customer_code'] ?? $codeGen->generateCustomerCode();
        
        // Insert Customer
        $customerId = $db->insert('customers', [
            'customer_code' => $customerCode,
            'company_name' => $input['company_name'] ?? $input['contact_person'],
            'contact_person' => $input['contact_person'] ?? '',
            'email' => $input['email'] ?? '',
            'phone' => $input['phone'] ?? '',
            'mobile' => $input['mobile'] ?? '',
            'gstin' => $input['gstin'] ?? null,
            'pan' => $input['pan'] ?? null,
            'credit_limit' => $input['credit_limit'] ?? 0,
            'payment_terms' => $input['payment_terms'] ?? 0,
            'customer_type' => $input['customer_type'],
            'company_id' => $user['company_id']
        ]);
        
        // Insert Address
        if (!empty($input['address_line1'])) {
            $db->insert('customer_addresses', [
                'customer_id' => $customerId,
                'address_type' => 'Both', // Default to Both
                'address_line1' => $input['address_line1'],
                'address_line2' => $input['address_line2'] ?? '',
                'city' => $input['city'] ?? '',
                'state' => $input['state'] ?? '',
                'country' => $input['country'] ?? 'India',
                'postal_code' => $input['postal_code'] ?? '',
                'is_default' => 1
            ]);
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Customer created successfully',
            'customer_id' => $customerId,
            'customer_code' => $customerCode,
            'company_name' => $input['company_name'] ?? $input['contact_person'],
            'contact_person' => $input['contact_person'] ?? ''
        ]);

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollback();
        }
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Method not allowed
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
exit;
