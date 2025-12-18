<?php
require_once '../../config/config.php';
require_once '../../classes/Database.php';

$db = Database::getInstance();

$sql = "CREATE TABLE IF NOT EXISTS system_broadcasts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'danger', 'success') DEFAULT 'info',
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

try {
    $db->conn->exec($sql);
    echo "Table 'system_broadcasts' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
