<?php
$pageTitle = 'Reset Database';
$currentPage = 'reset_database';
require_once '../../config/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';

$db = Database::getInstance();
$auth = new Auth();

// Check if user is super admin
if (!$auth->hasRole('Super Admin')) {
    header('Location: ' . BASE_URL . 'modules/dashboard/');
    exit;
}
$user = $auth->getCurrentUser();

$success = '';
$error = '';
$confirmationCode = '';

// Generate confirmation code
if (!isset($_SESSION['reset_confirmation_code'])) {
    $_SESSION['reset_confirmation_code'] = strtoupper(substr(md5(time()), 0, 6));
}
$confirmationCode = $_SESSION['reset_confirmation_code'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_database') {
    
    // Verify confirmation code
    if (!isset($_POST['confirmation_code']) || $_POST['confirmation_code'] !== $confirmationCode) {
        $error = 'Invalid confirmation code. Please enter the code shown above.';
    } else {
        try {
            $db->beginTransaction();
            
            // Get current super admin ID
            $superAdminId = $user['id'];

            // Disable Foreign Key Checks to allow truncating
            $db->getConnection()->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Get all tables dynamically
            $tables = $db->fetchAll("SHOW TABLES");
            
            // Loop through all tables
            foreach ($tables as $row) {
                // Get table name (first column of the row)
                $tableName = array_values($row)[0];
                
                // Skip migrations table if it exists
                if ($tableName === 'migrations') {
                    continue;
                }
                
                if ($tableName === 'users') {
                    // For users table, delete everyone except current super admin
                    $db->query("DELETE FROM users WHERE id != ?", [$superAdminId]);
                    
                    // Optional: Reset auto-increment if possible, or leave it. 
                    // Leaving it is safer to ensure ID continuity for the admin
                    // $db->query("ALTER TABLE users AUTO_INCREMENT = 1");
                } elseif ($tableName === 'roles' || $tableName === 'permissions' || $tableName === 'role_permissions') {
                    // OPTIONAL: Keep roles and permissions if they are system defaults?
                    // The user said "Empty all the tables". 
                    // But if we delete roles, the super admin (who has a role) might break if referenced by ID.
                    // Usually core metadata like 'roles' should be preserved or re-seeded.
                    // Given the request "Empty all the tables", I will clear them.
                    // The user might rely on seed scripts to re-populate.
                    // BUT super admin relies on 'user_roles' which links to 'roles'.
                    // If I delete 'roles', super admin loses their role name.
                    // I SHOULD PRESERVE SYSTEM ROLES if possible, or at least 'Super Admin'.
                    // However, to strictly follow "Empty all tables", I will truncate.
                    // User probably has a seeder or wants a blank slate.
                    // Wait, if I delete 'roles' and 'user_roles', the check `if ($user['role'] !== 'super_admin')`
                    // might fail on next login if role is gone.
                    // Actually, Auth.php checks session. Session persists until logout.
                    // But next login will fail.
                    // Let's assume user knows what they are doing or will re-seed.
                    // BUT, to be safe, I'll clear them.
                    $db->query("TRUNCATE TABLE `$tableName`");
                } else {
                    $db->query("TRUNCATE TABLE `$tableName`");
                }
            }
            // RE-SEED DEFAULT ROLES AND PERMISSIONS
            // 1. Create Roles
            $db->query("INSERT INTO roles (name, description) VALUES ('Super Admin', 'Full Access')");
            $superAdminRoleId = $db->getConnection()->lastInsertId();
            
            $db->query("INSERT INTO roles (name, description) VALUES ('Admin', 'Company Administrator')");
            $db->query("INSERT INTO roles (name, description) VALUES ('Employee', 'Standard User')");
            
            // 2. Assign Super Admin Role to preserved user
            $db->query("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)", [$superAdminId, $superAdminRoleId]);
            
            // Re-enable Foreign Key Checks
            $db->getConnection()->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            // Generate new confirmation code
            $_SESSION['reset_confirmation_code'] = strtoupper(substr(md5(time()), 0, 6));
            $confirmationCode = $_SESSION['reset_confirmation_code'];
            
            $success = 'Database has been successfully reset. All data has been cleared except your super admin account.';
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Error resetting database: ' . $e->getMessage();
        }
    }
}

require_once '../../includes/admin_layout.php';
?>

<style>
    .danger-zone {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .warning-card {
        background: #fef2f2;
        border: 2px solid #fca5a5;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .warning-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .warning-icon {
        width: 48px;
        height: 48px;
        background: #dc2626;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    
    .warning-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #991b1b;
        margin: 0;
    }
    
    .warning-text {
        color: #7f1d1d;
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    
    .warning-list {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1rem;
    }
    
    .warning-list h4 {
        color: #991b1b;
        margin-bottom: 1rem;
        font-size: 1rem;
    }
    
    .warning-list ul {
        margin: 0;
        padding-left: 1.5rem;
        color: #7f1d1d;
    }
    
    .warning-list li {
        margin-bottom: 0.5rem;
    }
    
    .reset-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 2rem;
    }
    
    .confirmation-box {
        background: #f9fafb;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        margin: 1.5rem 0;
    }
    
    .confirmation-code {
        font-size: 2rem;
        font-weight: 700;
        color: #dc2626;
        letter-spacing: 0.5rem;
        font-family: 'Courier New', monospace;
        margin: 1rem 0;
    }
    
    .form-field {
        margin-bottom: 1.5rem;
    }
    
    .form-field label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .form-field input {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        text-align: center;
        letter-spacing: 0.3rem;
        font-family: 'Courier New', monospace;
        text-transform: uppercase;
    }
    
    .form-field input:focus {
        outline: none;
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }
    
    .btn-danger-large {
        width: 100%;
        background: #dc2626;
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .btn-danger-large:hover {
        background: #b91c1c;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }
    
    .btn-secondary-link {
        display: inline-block;
        color: #6b7280;
        text-decoration: none;
        margin-top: 1rem;
        font-weight: 500;
    }
    
    .btn-secondary-link:hover {
        color: var(--text-primary);
    }
</style>

<div class="danger-zone">
    
    <div class="mb-4">
        <h1 class="h3 mb-1">Reset Database</h1>
        <p class="text-muted m-0">Clear all data from the system</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="warning-card">
        <div class="warning-header">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="warning-title">Danger Zone</h2>
        </div>
        
        <p class="warning-text">
            <strong>WARNING:</strong> This action will permanently delete ALL data from the database. 
            This operation cannot be undone and will result in complete data loss.
        </p>
        
        <div class="warning-list">
            <h4><i class="fas fa-trash-alt"></i> The following data will be DELETED:</h4>
            <ul>
                <li>All companies and their settings</li>
                <li>All users (except your super admin account)</li>
                <li>All customers and their information</li>
                <li>All products and categories</li>
                <li>All invoices, quotations, and orders</li>
                <li>All subscriptions</li>
                <li>All other business data</li>
            </ul>
        </div>
        
        <div class="warning-list" style="margin-top: 1rem; background: #ecfdf5; border: 1px solid #6ee7b7;">
            <h4 style="color: #065f46;"><i class="fas fa-shield-alt"></i> What will be preserved:</h4>
            <ul style="color: #065f46;">
                <li>Your super admin user account (<?php echo htmlspecialchars($user['email']); ?>)</li>
                <li>Database structure (tables and columns)</li>
            </ul>
        </div>
    </div>

    <div class="reset-card">
        <h3 style="margin-bottom: 1.5rem;">Confirm Database Reset</h3>
        
        <p style="color: #6b7280; margin-bottom: 1.5rem;">
            To proceed with resetting the database, please enter the confirmation code shown below:
        </p>
        
        <div class="confirmation-box">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">
                CONFIRMATION CODE
            </div>
            <div class="confirmation-code"><?php echo $confirmationCode; ?></div>
            <div style="color: #9ca3af; font-size: 0.75rem; margin-top: 0.5rem;">
                Enter this code exactly as shown
            </div>
        </div>
        
        <form method="POST" action="" onsubmit="return confirm('Are you ABSOLUTELY SURE you want to reset the entire database? This action CANNOT be undone!');">
            <input type="hidden" name="action" value="reset_database">
            
            <div class="form-field">
                <label>Enter Confirmation Code</label>
                <input 
                    type="text" 
                    name="confirmation_code" 
                    required 
                    placeholder="XXXXXX"
                    maxlength="6"
                    autocomplete="off"
                >
            </div>
            
            <button type="submit" class="btn-danger-large">
                <i class="fas fa-database"></i>
                Reset Database Now
            </button>
        </form>
        
        <div style="text-align: center;">
            <a href="companies.php" class="btn-secondary-link">
                <i class="fas fa-arrow-left"></i> Cancel and Go Back
            </a>
        </div>
    </div>

</div>

</div> <!-- End content-area -->
</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>
