// update_schema.php
// Consolidated database schema updates for Advanced Features
// Usage: php update_schema.php
<?php
// Mock HTTP_HOST for CLI to ensure local config is loaded
if (php_sapi_name() === 'cli' && !isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = '127.0.0.1';
}

require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Connected to database '" . DB_NAME . "'.\n\n";

    // 1. Create system_broadcasts table
    echo "1. Checking 'system_broadcasts' table...\n";
    $sqlBroadcasts = "CREATE TABLE IF NOT EXISTS system_broadcasts (
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
    $pdo->exec($sqlBroadcasts);
    $pdo->exec($sqlBroadcasts);
    echo "   - Table 'system_broadcasts' ensured.\n";

    // 1.1 Add target_company_id to system_broadcasts if missing
    echo "1.1 Checking 'target_company_id' in 'system_broadcasts'...\n";
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM system_broadcasts LIKE 'target_company_id'");
        if (!$stmt->fetch()) {
             $pdo->exec("ALTER TABLE system_broadcasts ADD COLUMN target_company_id INT DEFAULT NULL AFTER is_active");
             echo "   - Added 'target_company_id' column (NULL = Global).\n";
        } else {
             echo "   - 'target_company_id' column already exists.\n";
        }
    } catch (PDOException $e) {
        echo "   - Warning checking target_company_id: " . $e->getMessage() . "\n";
    }

    // 2. Create audit_logs table (if not exists)
    echo "2. Checking 'audit_logs' table...\n";
    $sqlAudit = "CREATE TABLE IF NOT EXISTS audit_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT,
        user_id INT,
        action VARCHAR(255) NOT NULL,
        table_name VARCHAR(100), -- Adapted from legacy 'resource_type'
        record_id INT,           -- Adapted from legacy 'resource_id'
        old_values JSON,         -- Adapted/New
        new_values JSON,         -- Adapted/New
        details JSON,            -- Legacy support
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sqlAudit);
    echo "   - Table 'audit_logs' ensured.\n";

    // 3. Update audit_logs schema: Add company_id if missing
    echo "3. Updating 'audit_logs' columns...\n";
    try {
        // Check if company_id exists
        $stmt = $pdo->query("SHOW COLUMNS FROM audit_logs LIKE 'company_id'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE audit_logs ADD COLUMN company_id INT AFTER id");
            echo "   - Added 'company_id' column.\n";
        } else {
            echo "   - 'company_id' column already exists.\n";
        }

        // Check for table_name vs resource_type normalization
        // If table was created with legacy schema, it might have resource_type but not table_name
        $stmt = $pdo->query("SHOW COLUMNS FROM audit_logs LIKE 'table_name'");
        if (!$stmt->fetch()) {
             $pdo->exec("ALTER TABLE audit_logs ADD COLUMN table_name VARCHAR(100) AFTER action");
             echo "   - Added 'table_name' column.\n";
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM audit_logs LIKE 'old_values'");
        if (!$stmt->fetch()) {
             $pdo->exec("ALTER TABLE audit_logs ADD COLUMN old_values JSON AFTER record_id");
             echo "   - Added 'old_values' column.\n";
        }
        
        $stmt = $pdo->query("SHOW COLUMNS FROM audit_logs LIKE 'new_values'");
        if (!$stmt->fetch()) {
             $pdo->exec("ALTER TABLE audit_logs ADD COLUMN new_values JSON AFTER old_values");
             echo "   - Added 'new_values' column.\n";
        }

    } catch (PDOException $e) {
        echo "   - Warning during column updates: " . $e->getMessage() . "\n";
    }

    // 4. Verify Roles tables (Dependencies for Permission Builder)
    echo "4. Checking Roles & Permissions tables...\n";
    // roles
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT,
        name VARCHAR(50) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    // permissions
    $pdo->exec("CREATE TABLE IF NOT EXISTS permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        module VARCHAR(50) NOT NULL,
        action VARCHAR(50) NOT NULL,
        description TEXT
    )");
    // role_permissions
    $pdo->exec("CREATE TABLE IF NOT EXISTS role_permissions (
        role_id INT NOT NULL,
        permission_id INT NOT NULL,
        PRIMARY KEY (role_id, permission_id)
    )");
     // user_roles (if not exists)
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_roles (
        user_id INT NOT NULL,
        role_id INT NOT NULL,
        PRIMARY KEY (user_id, role_id)
    )");
    echo "   - Roles tables ensured.\n";

    // 5. Update subscriptions table: Add cancelled_at if missing
    echo "5. Updating 'subscriptions' columns...\n";
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM subscriptions LIKE 'cancelled_at'");
        if (!$stmt->fetch()) {
             $pdo->exec("ALTER TABLE subscriptions ADD COLUMN cancelled_at DATETIME DEFAULT NULL AFTER status");
             echo "   - Added 'cancelled_at' column.\n";
        } else {
             echo "   - 'cancelled_at' column already exists.\n";
        }
    } catch (PDOException $e) {
        echo "   - Warning checking cancelled_at: " . $e->getMessage() . "\n";
    }

    echo "\nSchema update completed successfully.\n";

} catch (PDOException $e) {
    echo "\nCRITICAL ERROR: " . $e->getMessage() . "\n";
}
