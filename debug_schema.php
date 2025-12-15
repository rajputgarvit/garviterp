<?php
require_once 'config/config.php';
require_once 'classes/Database.php';

$db = Database::getInstance();

echo "<h1>Debug Data</h1>";

$userRoles = $db->fetchAll("SELECT * FROM user_roles WHERE user_id = 18");
echo "<h2>user_roles for User 18:</h2>";
echo "<pre>";
print_r($userRoles);
echo "</pre>";

$allRoles = $db->fetchAll("SELECT * FROM roles");
echo "<h2>All Roles:</h2>";
echo "<pre>";
print_r($allRoles);
echo "</pre>";

$user18 = $db->fetchAll("SELECT * FROM users WHERE id = 18");
echo "<h2>User 18:</h2>";
echo "<pre>";
print_r($user18);
echo "</pre>";
