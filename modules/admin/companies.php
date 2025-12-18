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
    } elseif ($_POST['action'] === 'assign_manual_subscription') {
        require_once '../../classes/Subscription.php';
        $subscription = new Subscription();
        try {
            $subscription->assignManualSubscription(
                $_POST['company_id'],
                $_POST['plan_name'],
                $_POST['start_date'],
                $_POST['end_date']
            );
            $success = "Subscription manually assigned successfully.";
        } catch (Exception $e) {
            $error = "Error assigning subscription: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'cancel_manual_subscription') {
        require_once '../../classes/Subscription.php';
        $subscription = new Subscription();
        try {
            $subscription->cancelSubscription($_POST['company_id']);
            $success = "Subscription cancelled successfully.";
        } catch (Exception $e) {
            $error = "Error cancelling subscription: " . $e->getMessage();
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
        (SELECT email FROM users u WHERE u.company_id = c.id ORDER BY u.created_at ASC LIMIT 1) as owner_email,
        s.plan_name,
        s.status as subscription_status,
        s.trial_ends_at
    FROM company_settings c
    LEFT JOIN subscriptions s ON c.id = s.company_id AND s.id = (
        SELECT MAX(id) FROM subscriptions WHERE company_id = c.id
    )
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
                    <th>Plan Type</th>
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
                    <td>
                        <?php if ($company['plan_name']): ?>
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($company['plan_name']); ?></div>
                            <?php if ($company['subscription_status'] === 'trial'): ?>
                                <?php 
                                    $daysLeft = ceil((strtotime($company['trial_ends_at']) - time()) / 86400); 
                                    $daysLeft = max(0, $daysLeft);
                                ?>
                                <span class="badge badge-warning" style="font-size: 0.8em;">Trial: <?php echo $daysLeft; ?> days</span>
                            <?php elseif ($company['subscription_status'] === 'active'): ?>
                                <span class="badge badge-success" style="font-size: 0.8em;">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger" style="font-size: 0.8em;"><?php echo ucfirst($company['subscription_status']); ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge badge-secondary">No Plan</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($company['created_at'])); ?></td>
                    <td>
                        <div style="display: flex; gap: 5px; align-items: center;">
                            <button onclick='openAssignModal(<?php echo json_encode($company); ?>)' class="btn btn-sm btn-info" title="Assign Plan">
                                <i class="fas fa-calendar-check"></i>
                            </button>

                            <?php if ($company['subscription_status'] === 'active' || $company['subscription_status'] === 'trial'): ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to CANCEL this subscription immediately?');" style="margin:0;">
                                <input type="hidden" name="action" value="cancel_manual_subscription">
                                <input type="hidden" name="company_id" value="<?php echo $company['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-warning" title="Cancel Subscription">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <a href="company_details.php?id=<?php echo $company['id']; ?>" class="btn btn-sm btn-secondary" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            <form method="POST" onsubmit="return confirm('Are you sure you want to PERMANENTLY delete this company? This will delete ALL users, subscriptions, and data associated with it. This action CANNOT be undone.');" style="margin:0;">
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

<!-- Assign Plan Modal -->
<div id="assignPlanModal" class="modal" style="display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); transition: all 0.3s ease;">
    <div class="modal-content" style="background-color: #fff; margin: 5% auto; padding: 0; border: none; width: 90%; max-width: 600px; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative; animation: slideIn 0.3s ease-out;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 24px 32px; border-bottom: 1px solid #f3f4f6;">
            <div>
                 <h3 style="margin: 0; font-size: 1.5rem; color: #1f2937; font-weight: 700; letter-spacing: -0.025em;">Assign Plan</h3>
                 <p style="margin: 4px 0 0 0; color: #6b7280; font-size: 0.875rem;">Manually grant subscription access</p>
            </div>
            <span onclick="document.getElementById('assignPlanModal').style.display='none'" style="cursor: pointer; font-size: 24px; color: #9ca3af; transition: color 0.2s; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%;" onmouseover="this.style.backgroundColor='#f3f4f6'; this.style.color='#374151'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#9ca3af'">&times;</span>
        </div>
        <form method="POST" style="padding: 32px;">
            <input type="hidden" name="action" value="assign_manual_subscription">
            <input type="hidden" name="company_id" id="assign_company_id">
            
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151; font-size: 0.95rem;">Company</label>
                <div style="position: relative;">
                    <i class="fas fa-building" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                    <input type="text" id="assign_company_name" class="form-control" readonly style="background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 12px 12px 12px 40px; border-radius: 8px; width: 100%; box-sizing: border-box; color: #4b5563; font-weight: 500;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151; font-size: 0.95rem;">Select Plan</label>
                <div style="position: relative;">
                     <i class="fas fa-crown" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                    <select name="plan_name" class="form-control" required style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #e5e7eb; border-radius: 8px; background-color: #fff; box-sizing: border-box; font-size: 1rem; color: #111827; appearance: none; -webkit-appearance: none;">
                        <?php
                        $plans = $db->fetchAll("SELECT plan_name FROM subscription_plans WHERE is_active = 1");
                        foreach ($plans as $plan) {
                            echo "<option value='" . htmlspecialchars($plan['plan_name']) . "'>" . htmlspecialchars($plan['plan_name']) . "</option>";
                        }
                        ?>
                    </select>
                    <i class="fas fa-chevron-down" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none;"></i>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 32px;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151; font-size: 0.95rem;">Start Date</label>
                    <input type="datetime-local" name="start_date" class="form-control" required value="<?php echo date('Y-m-d\TH:i'); ?>" style="width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; box-sizing: border-box; font-family: inherit;">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151; font-size: 0.95rem;">End Date</label>
                    <input type="datetime-local" name="end_date" class="form-control" required value="<?php echo date('Y-m-d\TH:i', strtotime('+1 month')); ?>" style="width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; box-sizing: border-box; font-family: inherit;">
                </div>
            </div>

            <div style="text-align: right; padding-top: 24px; border-top: 1px solid #f3f4f6; display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="document.getElementById('assignPlanModal').style.display='none'" class="btn" style="padding: 10px 20px; border: 1px solid #e5e7eb; background: #fff; border-radius: 8px; font-weight: 600; color: #374151; cursor: pointer; transition: all 0.2s;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background-color: #2563eb; border: none; color: white; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2); transition: all 0.2s;">Assign Plan</button>
            </div>
        </form>
    </div>
    <style>
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</div>

<script>
function openAssignModal(company) {
    document.getElementById('assign_company_id').value = company.id;
    document.getElementById('assign_company_name').value = company.company_name;
    document.getElementById('assignPlanModal').style.display = 'block';
}
</script>
</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>
