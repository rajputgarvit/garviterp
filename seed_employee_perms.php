<?php
require_once 'config/config.php';
require_once 'classes/Database.php';

$db = Database::getInstance();
echo "<h1>Seeding Employee Permissions</h1>";

// 1. Get Employee Role ID
$role = $db->fetchOne("SELECT id FROM roles WHERE name = 'Employee'");
if (!$role) {
    die("Employee role not found. Please run the reset database script first to create default roles.");
}
$roleId = $role['id'];
echo "Found Employee Role ID: $roleId<br>";

// 2. Clear existing permissions for this role (optional, but good for idempotency)
$db->delete('role_permissions', 'role_id = ?', [$roleId]);
echo "Cleared existing permissions.<br>";

// 3. Define default permissions to assign (Module => Actions)
$defaults = [
    'dashboard' => ['view'],
    'sales' => ['view', 'create'],
    'inventory' => ['view'],
    'hrm' => ['view'] // Self-service usually
];

$count = 0;
foreach ($defaults as $module => $actions) {
    foreach ($actions as $action) {
        // Find permission ID
        $perm = $db->fetchOne("SELECT id FROM permissions WHERE module = ? AND action = ?", [$module, $action]);
        if ($perm) {
            $db->insert('role_permissions', [
                'role_id' => $roleId,
                'permission_id' => $perm['id']
            ]);
            $count++;
            echo "Assigned $module.$action<br>";
        } else {
            echo "Warning: Permission $module.$action not found in database.<br>";
        }
    }
}

echo "<h3>Successfully assigned $count permissions to Employee role.</h3>";
echo "You should now see these listed in the User Edit page.";
