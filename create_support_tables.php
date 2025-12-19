<?php
// Mock HTTP_HOST for CLI to ensure local config is loaded
$_SERVER['HTTP_HOST'] = '127.0.0.1';
require_once 'config/config.php';
require_once 'classes/Database.php';

try {
    $db = Database::getInstance();
    
    // 1. support_categories
    $db->query("
        CREATE TABLE IF NOT EXISTS support_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table 'support_categories' created/verified.\n";

    // Seed default categories
    $categories = ['Technical Issue', 'Billing & Payments', 'General Inquiry', 'Feature Request'];
    foreach ($categories as $cat) {
        $exists = $db->fetchOne("SELECT id FROM support_categories WHERE name = ?", [$cat]);
        if (!$exists) {
            $db->query("INSERT INTO support_categories (name) VALUES (?)", [$cat]);
            echo "Seeded category: $cat\n";
        }
    }

    // 2. support_tickets
    $db->query("
        CREATE TABLE IF NOT EXISTS support_tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_number VARCHAR(50) NOT NULL UNIQUE,
            user_id INT NOT NULL,
            category_id INT NOT NULL,
            subject VARCHAR(255) NOT NULL,
            priority ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
            status ENUM('Open', 'In Progress', 'Awaiting Reply', 'Resolved', 'Closed') DEFAULT 'Open',
            assigned_to INT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES support_categories(id),
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table 'support_tickets' created/verified.\n";

    // 3. support_ticket_replies
    $db->query("
        CREATE TABLE IF NOT EXISTS support_ticket_replies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT NOT NULL,
            user_id INT NOT NULL,
            message TEXT NOT NULL,
            attachment_path VARCHAR(255) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table 'support_ticket_replies' created/verified.\n";

    echo "Support system database setup completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
