<?php
require_once 'config/config.php';
require_once 'classes/Database.php';

$db = Database::getInstance();

echo "<h1>Table Schema: company_settings</h1>";
try {
    $columns = $db->fetchAll("DESCRIBE company_settings");
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
