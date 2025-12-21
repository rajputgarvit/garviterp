<?php
// update_db_industry.php
// Temporary script to patch company_settings table for Industry Type

if (php_sapi_name() === 'cli' && !isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = '127.0.0.1';
}

require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Connected to database.\n";
    
    // Check and add industry_type
    echo "Checking 'industry_type'...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM company_settings LIKE 'industry_type'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE company_settings ADD COLUMN industry_type VARCHAR(100) DEFAULT NULL AFTER company_name");
        echo " - Added 'industry_type' column.\n";
    } else {
        echo " - Column exists.\n";
    }
    
    echo "Done.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
