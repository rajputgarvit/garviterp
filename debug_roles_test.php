<?php
require_once 'config/config.php';
require_once 'classes/Database.php';

$db = Database::getInstance();
echo "<h1>Role Insert Test</h1>";

$userId = 18;
// Fetch a role ID
$role = $db->fetchOne("SELECT id FROM roles LIMIT 1");
if (!$role) {
    die("No roles found");
}
$roleId = $role['id'];

echo "Trying to insert user_id=$userId, role_id=$roleId<br>";

try {
    // Check if exists first
    $exists = $db->fetchOne("SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?", [$userId, $roleId]);
    if ($exists) {
        echo "Role already exists, deleting...<br>";
        $db->delete("user_roles", "user_id = ? AND role_id = ?", [$userId, $roleId]);
    }

    $id = $db->insert("user_roles", [
        'user_id' => $userId, 
        'role_id' => $roleId
    ]);
    echo "Insert returned ID: $id<br>";
    echo "Insert Success!<br>";
    
    // Verify
    $check = $db->fetchOne("SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?", [$userId, $roleId]);
    echo "Verification: ";
    print_r($check);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
