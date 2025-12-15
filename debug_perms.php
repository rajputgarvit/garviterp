<?php
require_once 'config/config.php';
require_once 'classes/Database.php';

$db = Database::getInstance();
echo "<h1>Role Permissions Check</h1>";

// Get Employee Role ID
$role = $db->fetchOne("SELECT * FROM roles WHERE name = 'Employee'");
if (!$role) {
    die("Employee role not found");
}
echo "Employee Role ID: " . $role['id'] . "<br>";

// Get Permissions for it
$perms = $db->fetchAll("
    SELECT p.module, p.action 
    FROM role_permissions rp
    JOIN permissions p ON rp.permission_id = p.id
    WHERE rp.role_id = ?
", [$role['id']]);

if (empty($perms)) {
    echo "NO PERMISSIONS found for Employee role.<br>";
} else {
    echo "Permissions found:<br><pre>";
    print_r($perms);
    echo "</pre>";
}

// Also check all permissions
$allPerms = $db->fetchAll("SELECT count(*) as c FROM permissions");
echo "Total permissions in system: " . $allPerms['c'];
