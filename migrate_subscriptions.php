<?php
require_once 'config/config.php';
require_once 'classes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h1>Migrating Subscriptions Table</h1>";

try {
    $db->beginTransaction();

    // 1. Add company_id column if it doesn't exist
    $columns = $db->fetchAll("DESCRIBE subscriptions");
    $hasCompanyId = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'company_id') {
            $hasCompanyId = true;
            break;
        }
    }

    if (!$hasCompanyId) {
        echo "Adding company_id column...<br>";
        $db->query("ALTER TABLE subscriptions ADD COLUMN company_id INT(11) AFTER user_id");
        echo "Column added.<br>";
    } else {
        echo "company_id column already exists.<br>";
    }

    // 2. Populate company_id from users table
    echo "Populating company_id...<br>";
    $db->query("
        UPDATE subscriptions s
        JOIN users u ON s.user_id = u.id
        SET s.company_id = u.company_id
        WHERE s.company_id IS NULL
    ");
    echo "company_id populated.<br>";

    // 3. Make company_id NOT NULL and Add Index
    echo "Modifying column to NOT NULL and adding index...<br>";
    // We can only make it NOT NULL if all records have a company_id. 
    // Let's check for any orphans first (subscriptions with users that don't have company_id or users deleted)
    $orphans = $db->fetchOne("SELECT COUNT(*) as count FROM subscriptions WHERE company_id IS NULL");
    
    if ($orphans['count'] == 0) {
        $db->query("ALTER TABLE subscriptions MODIFY COLUMN company_id INT(11) NOT NULL");
        $db->query("CREATE INDEX idx_company_id ON subscriptions(company_id)");
        echo "Column modified and indexed.<br>";
    } else {
        echo "WARNING: Found {$orphans['count']} subscriptions with null company_id. Skipping NOT NULL constraint.<br>";
    }

    $db->commit();
    echo "<h2>Migration Completed Successfully</h2>";

} catch (Exception $e) {
    $db->rollBack();
    echo "<h2>Migration Failed: " . $e->getMessage() . "</h2>";
}
