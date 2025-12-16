<?php
require_once 'config/config.php';
require_once 'classes/Database.php';

$db = Database::getInstance();

echo "<h1>Table Schema: subscriptions</h1>";
try {
    $columns = $db->fetchAll("DESCRIBE subscriptions");
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
