<?php
$pageTitle = 'Company Details';
$currentPage = 'companies';
require_once '../../config/config.php';
require_once '../../includes/admin_layout.php';

$db = Database::getInstance();
$companyId = $_GET['id'] ?? null;

if (!$companyId) {
    header('Location: companies.php');
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_company'])) {
    $db->update('company_settings', [
        'company_name' => $_POST['company_name'] ?? '',
        'address_line1' => $_POST['address_line1'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'gstin' => $_POST['gstin'] ?? ''
    ], 'id = ?', [$companyId]);
    $success = "Company details updated successfully.";
}

// Fetch Company Details
$company = $db->fetchOne("SELECT * FROM company_settings WHERE id = ?", [$companyId]);

if (!$company) {
    echo "Company not found.";
    exit;
}

// Fetch Active Subscription
$subscription = $db->fetchOne("SELECT * FROM subscriptions WHERE company_id = ? AND status IN ('active', 'trial') ORDER BY id DESC LIMIT 1", [$companyId]);

// Helper function to safely get count
function getCount($db, $table, $companyId) {
    try {
        // limit 1 check to ensure table exists not needed if we trust schema, but for safety in generic SQL:
        // Actually, try-catch on the query is best.
        $result = $db->fetchOne("SELECT COUNT(*) as count FROM `$table` WHERE company_id = ?", [$companyId]);
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        return 0; // Return 0 if table doesn't exist
    }
}

// Fetch Subscription History
$subscriptionHistory = $db->fetchAll(
    "SELECT * FROM subscriptions WHERE company_id = ? ORDER BY created_at DESC",
    [$companyId]
);

// Fetch Transaction History
$transactionHistory = $db->fetchAll(
    "SELECT pt.*, s.plan_name 
     FROM payment_transactions pt 
     JOIN subscriptions s ON pt.subscription_id = s.id 
     WHERE s.company_id = ? 
     ORDER BY pt.transaction_date DESC",
    [$companyId]
);

// Fetch Statistics
$stats = [
    'hr' => [
        'employees' => getCount($db, 'employees', $companyId),
        'departments' => getCount($db, 'departments', $companyId),
        'leaves' => getCount($db, 'leaves', $companyId) // Assuming 'leaves' or 'leave_applications'
    ],
    'inventory' => [
        'products' => getCount($db, 'products', $companyId),
        'warehouses' => getCount($db, 'warehouses', $companyId),
    ],
    'sales' => [
        'customers' => getCount($db, 'customers', $companyId),
        'quotations' => getCount($db, 'quotations', $companyId),
        'orders' => getCount($db, 'sales_orders', $companyId),
        'invoices' => getCount($db, 'invoices', $companyId),
        'leads' => getCount($db, 'leads', $companyId)
    ],
    'purchase' => [
        'suppliers' => getCount($db, 'suppliers', $companyId),
        'orders' => getCount($db, 'purchase_orders', $companyId),
        'invoices' => getCount($db, 'purchase_invoices', $companyId)
    ],
    'accounting' => [
        'chart_of_accounts' => getCount($db, 'chart_of_accounts', $companyId),
        'journal_entries' => getCount($db, 'journal_entries', $companyId)
    ],
    'crm' => [
        'leads' => getCount($db, 'leads', $companyId) // Duplicate of sales leads, but requested separately
    ]
];

// Fetch Company Users
$users = $db->fetchAll("
    SELECT u.*, 
           (SELECT COUNT(*) FROM subscriptions s WHERE s.user_id = u.id AND s.status = 'active') as has_active_sub,
           r.name as role
    FROM users u 
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    WHERE u.company_id = ? 
    ORDER BY u.created_at DESC
", [$companyId]);
?>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .module-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #eee;
        overflow: hidden;
    }
    .module-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .module-title {
        font-weight: 600;
        color: #333;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .module-content {
        padding: 20px;
    }
    .stat-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .stat-row:last-child {
        border-bottom: none;
    }
    .stat-label {
        color: #666;
    }
    .stat-value {
        font-weight: 600;
        color: #333;
    }
    .company-header {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        margin-bottom: 25px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
</style>

<div class="company-header">
    <div>
        <h2 style="margin: 0 0 10px 0;"><?php echo htmlspecialchars($company['company_name']); ?></h2>
        <div style="color: #666; margin-bottom: 5px;">
            <i class="fas fa-envelope" style="width: 20px;"></i> <?php echo htmlspecialchars($company['email'] ?? 'N/A'); ?>
        </div>
        <div style="color: #666;">
            <i class="fas fa-phone" style="width: 20px;"></i> <?php echo htmlspecialchars($company['phone'] ?? 'N/A'); ?>
        </div>
        <?php if (!empty($company['gstin'])): ?>
        <div style="color: #666; margin-top: 5px;">
            <i class="fas fa-building" style="width: 20px;"></i> GSTIN: <?php echo htmlspecialchars($company['gstin']); ?>
        </div>
        <?php endif; ?>
    </div>
    <div style="text-align: right;">
        <div style="margin-bottom: 10px;">
            <span class="badge badge-<?php echo ($subscription && $subscription['status'] === 'active') ? 'success' : 'warning'; ?>" style="font-size: 1rem; padding: 8px 15px;">
                <?php echo $subscription ? ucfirst($subscription['status']) : 'No Active Plan'; ?>
            </span>
        </div>
        <?php if ($subscription): ?>
            <div style="color: #666;">Plan: <strong><?php echo htmlspecialchars($subscription['plan_name']); ?></strong></div>
            <div style="color: #888; font-size: 0.9em;">Valid until: <?php echo date('d M Y', strtotime($subscription['current_period_end'] ?? $subscription['trial_ends_at'])); ?></div>
        <?php endif; ?>
        <div style="margin-top: 15px;">
            <button onclick="document.getElementById('editCompanyModal').style.display='block'" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit"></i> Edit Details
            </button>
        </div>
    </div>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="dashboard-grid">
    <!-- HR Management -->
    <div class="module-card">
        <div class="module-header">
            <h3 class="module-title"><i class="fas fa-users" style="color: #4a90e2;"></i> HR Management</h3>
        </div>
        <div class="module-content">
            <div class="stat-row">
                <span class="stat-label">Employees</span>
                <span class="stat-value"><?php echo number_format($stats['hr']['employees']); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Departments</span>
                <span class="stat-value"><?php echo number_format($stats['hr']['departments']); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Leaves Taken</span>
                <span class="stat-value"><?php echo number_format($stats['hr']['leaves']); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Payroll</span>
                <span class="stat-value" style="font-size: 0.85rem; font-weight: 500; color: #666;"><i class="fas fa-check-circle text-success" style="font-size: 0.85rem;"></i> Enabled</span>
            </div>
        </div>
    </div>

    <!-- Sales -->
    <div class="module-card">
        <div class="module-header">
            <h3 class="module-title"><i class="fas fa-shopping-cart" style="color: #e24a4a;"></i> Sales</h3>
        </div>
        <div class="module-content">
            <div class="stat-row">
                <span class="stat-label">Customers</span>
                <span class="stat-value"><?php echo number_format($stats['sales']['customers']); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Quotations</span>
                <span class="stat-value"><?php echo number_format($stats['sales']['quotations']); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Sales Orders</span>
                <span class="stat-value"><?php echo number_format($stats['sales']['orders']); ?></span>
            </div>
             <div class="stat-row">
                <span class="stat-label">Invoices</span>
                <span class="stat-value"><?php echo number_format($stats['sales']['invoices']); ?></span>
            </div>
        </div>
    </div>

    <!-- Inventory -->
    <div class="module-card">
        <div class="module-header">
            <h3 class="module-title"><i class="fas fa-boxes" style="color: #f39c12;"></i> Inventory</h3>
        </div>
        <div class="module-content">
            <div class="stat-row">
                <span class="stat-label">Products</span>
                <span class="stat-value"><?php echo number_format($stats['inventory']['products']); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Warehouses</span>
                <span class="stat-value"><?php echo number_format($stats['inventory']['warehouses']); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Stock Management</span>
                <span class="stat-value" style="font-size: 0.85rem; font-weight: 500; color: #666;"><i class="fas fa-check-circle text-success" style="font-size: 0.85rem;"></i> Enabled</span>
            </div>
        </div>
    </div>

    <!-- Purchase -->
    <div class="module-card">
        <div class="module-header">
            <h3 class="module-title"><i class="fas fa-shopping-bag" style="color: #8e44ad;"></i> Purchase</h3>
        </div>
        <div class="module-content">
            <div class="stat-row">
                <span class="stat-label">Suppliers</span>
                <span class="stat-value"><?php echo number_format($stats['purchase']['suppliers']); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Purchase Orders</span>
                <span class="stat-value"><?php echo number_format($stats['purchase']['orders']); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Purchase Invoices</span>
                <span class="stat-value"><?php echo number_format($stats['purchase']['invoices']); ?></span>
            </div>
        </div>
    </div>

    <!-- Accounting -->
    <div class="module-card">
        <div class="module-header">
            <h3 class="module-title"><i class="fas fa-calculator" style="color: #27ae60;"></i> Accounting</h3>
        </div>
        <div class="module-content">
            <div class="stat-row">
                <span class="stat-label">Chart of Accounts</span>
                <span class="stat-value"><?php echo number_format($stats['accounting']['chart_of_accounts']); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Journal Entries</span>
                <span class="stat-value"><?php echo number_format($stats['accounting']['journal_entries']); ?></span>
            </div>
             <div class="stat-row">
                <span class="stat-label">Financial Reports</span>
                <span class="stat-value" style="font-size: 0.85rem; font-weight: 500; color: #666;"><i class="fas fa-check-circle text-success" style="font-size: 0.85rem;"></i> Enabled</span>
            </div>
        </div>
    </div>

    <!-- CRM -->
    <div class="module-card">
        <div class="module-header">
            <h3 class="module-title"><i class="fas fa-users-cog" style="color: #e67e22;"></i> CRM</h3>
        </div>
        <div class="module-content">
            <div class="stat-row">
                <span class="stat-label">Leads</span>
                <span class="stat-value"><?php echo number_format($stats['crm']['leads']); ?></span>
            </div>
        </div>
    </div>
    
    <!-- System -->
    <div class="module-card">
        <div class="module-header">
            <h3 class="module-title"><i class="fas fa-cogs" style="color: #7f8c8d;"></i> System</h3>
        </div>
        <div class="module-content">
             <div class="stat-row">
                <span class="stat-label">Company Settings</span>
                <span class="stat-value" style="font-size: 0.85rem; font-weight: 500; color: #666;"><i class="fas fa-check-circle text-success" style="font-size: 0.85rem;"></i> Configured</span>
            </div>
             <div class="stat-row">
                <span class="stat-label">System Settings</span>
                <span class="stat-value" style="font-size: 0.85rem; font-weight: 500; color: #666;"><i class="fas fa-check-circle text-success" style="font-size: 0.85rem;"></i> Default</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Associated Users</div>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div style="font-weight: 500;"><?php echo htmlspecialchars($user['full_name']); ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);">@<?php echo htmlspecialchars($user['username']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role'] ?? 'N/A'); ?></td>
                    <td>
                        <?php if ($user['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="users.php" class="btn btn-sm btn-secondary">Manage</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

    <!-- Subscription History -->
    <div class="card mt-4" style="margin-top: 25px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #eee;">
        <div class="card-header" style="background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #eee;">
            <h3 style="margin: 0; font-size: 1.1rem; color: #333;"><i class="fas fa-history"></i> Subscription History</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Plan</th>
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Status</th>
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Billing Cycle</th>
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Amount</th>
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Start Date</th>
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">End Date</th>
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Cancelled At</th>
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subscriptionHistory)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-3" style="padding: 15px; text-align: center; color: #999;">No subscription history found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($subscriptionHistory as $sub): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 12px;">
                                        <span class="fw-bold" style="font-weight: 600;"><?php echo htmlspecialchars(ucfirst($sub['plan_name'])); ?></span>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php
                                            $statusClass = match($sub['status']) {
                                                'active' => 'success',
                                                'trial' => 'info',
                                                'cancelled' => 'danger',
                                                'expired' => 'secondary',
                                                default => 'primary'
                                            };
                                        ?>
                                        <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($sub['status']); ?></span>
                                    </td>
                                    <td style="padding: 12px;"><?php echo ucfirst($sub['billing_cycle']); ?></td>
                                    <td style="padding: 12px;">₹<?php echo number_format($sub['plan_price'], 2); ?></td>
                                    <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($sub['current_period_start'])); ?></td>
                                    <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($sub['current_period_end'])); ?></td>
                                    <td style="padding: 12px;">
                                        <?php if ($sub['status'] === 'cancelled'): ?>
                                            <span class="text-danger small"><?php echo date('M d, Y H:i', strtotime($sub['updated_at'])); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small" style="padding: 12px; color: #888; font-size: 0.85rem;"><?php echo date('M d, Y H:i', strtotime($sub['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card mt-4" style="margin-top: 25px; margin-bottom: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #eee;">
        <div class="card-header" style="background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #eee;">
            <h3 style="margin: 0; font-size: 1.1rem; color: #333;"><i class="fas fa-receipt"></i> Transaction History</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Date</th>
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Transaction ID</th>
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Plan</th>
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Amount</th>
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Status</th>
                            <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #666; font-weight: 600;">Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactionHistory)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3" style="padding: 15px; text-align: center; color: #999;">No transactions found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($transactionHistory as $txn): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 12px;"><?php echo date('M d, Y H:i', strtotime($txn['transaction_date'])); ?></td>
                                    <td class="font-monospace small" style="padding: 12px; font-family: monospace; color: #555;"><?php echo htmlspecialchars($txn['razorpay_payment_id'] ?? '-'); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars(ucfirst($txn['plan_name'])); ?></td>
                                    <td style="padding: 12px;">
                                        <?php echo '₹' . number_format($txn['amount'], 2); ?>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php
                                            $statusClass = match($txn['status']) {
                                                'captured', 'success' => 'success',
                                                'authorized' => 'primary',
                                                'failed' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($txn['status']); ?></span>
                                    </td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars(ucfirst($txn['payment_method'] ?? '-')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div> <!-- End content-area -->

<!-- Edit Company Modal -->
<div id="editCompanyModal" class="modal" style="display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
    <div class="modal-content" style="background-color: #fff; margin: 5% auto; padding: 0; border: none; width: 90%; max-width: 600px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; border-bottom: 1px solid #eee;">
            <h3 style="margin: 0; font-size: 1.25rem;">Edit Company Details</h3>
            <span onclick="document.getElementById('editCompanyModal').style.display='none'" style="cursor: pointer; font-size: 24px;">&times;</span>
        </div>
        <form method="POST" style="padding: 25px;">
            <input type="hidden" name="update_company" value="1">
            <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($company['company_name']); ?>" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>GST Number</label>
                    <input type="text" name="gstin" class="form-control" value="<?php echo htmlspecialchars($company['gstin'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Address</label>
                <textarea name="address_line1" class="form-control" rows="3"><?php echo htmlspecialchars($company['address_line1'] ?? ''); ?></textarea>
            </div>
            <div style="text-align: right;">
                <button type="button" onclick="document.getElementById('editCompanyModal').style.display='none'" class="btn btn-secondary" style="margin-right: 10px;">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>
