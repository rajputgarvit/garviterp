<?php
$pageTitle = 'Company Management';
$currentPage = 'companies';
require_once '../../config/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';

$db = Database::getInstance();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['company_id'])) {
    $companyId = $_POST['company_id'];
    
    if ($_POST['action'] === 'delete_company') {
        try {
            $db->beginTransaction();
            
            // 1. Delete Subscriptions (via users) - Find users first
            $userIds = $db->fetchAll("SELECT id FROM users WHERE company_id = ?", [$companyId]);
            $userIdsArray = array_column($userIds, 'id');
            
            if (!empty($userIdsArray)) {
                $placeholders = str_repeat('?,', count($userIdsArray) - 1) . '?';
                $db->delete("subscriptions", "user_id IN ($placeholders)", $userIdsArray);
                
                // 2. Delete Users
                $db->delete("users", "company_id = ?", [$companyId]);
            }
            
            // 3. Delete Company Settings (The tenant)
            $db->delete("company_settings", "id = ?", [$companyId]);
            
            $db->commit();
            $success = "Company and related data deleted successfully.";
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Error deleting company: " . $e->getMessage();
        }
    }
}

require_once '../../includes/admin_layout.php';

// Fetch Companies with stats
$companies = $db->fetchAll("
    SELECT 
        c.id, 
        c.company_name, 
        c.created_at,
        (SELECT COUNT(*) FROM users u WHERE u.company_id = c.id) as user_count,
        (SELECT full_name FROM users u WHERE u.company_id = c.id ORDER BY u.created_at ASC LIMIT 1) as owner_name,
        (SELECT email FROM users u WHERE u.company_id = c.id ORDER BY u.created_at ASC LIMIT 1) as owner_email
    FROM company_settings c
    ORDER BY c.created_at DESC
");
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">Registered Companies (Tenants)</div>
        <a href="company_create.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Company
        </a>
    </div>
    <div class="table-responsive">
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($companies)): ?>
            <div style="text-align: center; padding: 50px 20px;">
                <i class="fas fa-building" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 20px;"></i>
                <h3>No Companies Found</h3>
                <p style="color: var(--text-secondary); margin-bottom: 30px;">Get started by creating your first company/tenant.</p>
                <a href="company_create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Company
                </a>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Owner</th>
                    <th>Users</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $company): ?>
                <tr>
                    <td>
                        <div style="font-weight: 500;"><?php echo htmlspecialchars($company['company_name']); ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);">ID: <?php echo $company['id']; ?></div>
                    </td>
                    <td>
                        <div><?php echo htmlspecialchars($company['owner_name'] ?? 'N/A'); ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($company['owner_email'] ?? 'N/A'); ?></div>
                    </td>
                    <td>
                        <span class="badge badge-secondary">
                            <i class="fas fa-users"></i> <?php echo $company['user_count']; ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($company['created_at'])); ?></td>
                    <td>
                        <div style="display: flex; gap: 5px; align-items: center;">
                            <a href="company_details.php?id=<?php echo $company['id']; ?>" class="btn btn-sm btn-secondary" title="View Details">
                                <i class="fas fa-eye"></i> View
                            </a>
                            
                            <form method="POST" onsubmit="return confirm('Are you sure you want to PERMANENTLY delete this company? This will delete ALL users, subscriptions, and data associated with it. This action CANNOT be undone.');">
                                <input type="hidden" name="action" value="delete_company">
                                <input type="hidden" name="company_id" value="<?php echo $company['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete Company">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

</div> <!-- End content-area -->
</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>
