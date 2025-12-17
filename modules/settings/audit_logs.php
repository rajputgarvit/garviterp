<?php
$currentPage = 'audit_logs';
require_once '../../config/config.php';
require_once '../../includes/admin_layout.php';
require_once '../../classes/Logger.php';

$user = $currentUser;
?>

<div>

<?php
$logger = new Logger();
$db = Database::getInstance();

// Filters
$targetUserId = $_GET['user_id'] ?? '';
$action = $_GET['action'] ?? '';
$limit = 50;

// Build Query
$query = "
    SELECT al.*, u.full_name, u.email 
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.id
    WHERE al.company_id = ?
";
$params = [$user['company_id']];

if ($targetUserId) {
    $query .= " AND al.user_id = ?";
    $params[] = $targetUserId;
}

if ($action) {
    $query .= " AND al.action LIKE ?";
    $params[] = "%$action%";
}

$query .= " ORDER BY al.created_at DESC LIMIT $limit";

// Execute
$logs = $db->fetchAll($query, $params);

// Fetch company users for filter dropdown
$companyUsers = $db->fetchAll("SELECT id, full_name FROM users WHERE company_id = ? ORDER BY full_name", [$user['company_id']]);

?>

<div class="container-fluid" style="padding: 20px;">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">Activity Logs</div>
            <a href="../admin/settings.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back to Settings</a>
        </div>
        
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4 p-3 bg-light rounded" style="background: #f8f9fa;">
                <div class="row" style="align-items: flex-end; --bs-gutter-x: 1.5rem;">
                    <div class="col-md-3">
                        <label class="form-label" style="font-weight: 500; font-size: 0.9rem;">Filter by User</label>
                        <select name="user_id" class="form-control form-select">
                            <option value="">All Users</option>
                            <?php foreach ($companyUsers as $u): ?>
                                <option value="<?php echo $u['id']; ?>" <?php echo $targetUserId == $u['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($u['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-weight: 500; font-size: 0.9rem;">Filter by Action</label>
                        <input type="text" name="action" class="form-control" value="<?php echo htmlspecialchars($action); ?>" placeholder="e.g. login, create_invoice">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                        <a href="audit_logs.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Time</th>
                            <th style="width: 20%;">User</th>
                            <th style="width: 20%;">Action</th>
                            <th style="width: 35%;">Details</th>
                            <th style="width: 10%;">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No activity logs found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <?php 
                                    $details = json_decode($log['new_values'], true);
                                    $detailText = isset($details['details']) ? $details['details'] : '';
                                    if (empty($detailText) && isset($log['table_name'])) {
                                        $detailText = $log['table_name'] . ($log['record_id'] ? " #{$log['record_id']}" : '');
                                    }
                                ?>
                                <tr>
                                    <td><?php echo date('M j, H:i', strtotime($log['created_at'])); ?></td>
                                    <td>
                                        <?php if ($log['full_name']): ?>
                                            <div><strong><?php echo htmlspecialchars($log['full_name']); ?></strong></div>
                                        <?php else: ?>
                                            <span class="text-muted">System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-info text-dark badge-info"><?php echo htmlspecialchars($log['action']); ?></span></td>
                                    <td class="small text-muted">
                                        <?php echo htmlspecialchars($detailText ? (is_array($detailText) ? json_encode($detailText) : $detailText) : '-'); ?>
                                    </td>
                                    <td><code class="small"><?php echo htmlspecialchars($log['ip_address'] ?? 'CLI'); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>

</div>
</div><!-- End content-area -->
</main><!-- End main-content -->
</div><!-- End dashboard-wrapper -->

<?php require_once '../../includes/footer.php'; ?>
