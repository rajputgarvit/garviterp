<?php
$pageTitle = 'Edit User';
$currentPage = 'users';
require_once '../../config/config.php';
require_once '../../includes/admin_layout.php';

$db = Database::getInstance();
$userId = $_GET['id'] ?? null;

if (!$userId) {
    header('Location: users.php');
    exit;
}

    // Handle Update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
        try {
            $db->beginTransaction();

            $db->update('users', [
                'full_name' => $_POST['full_name'],
                'email' => $_POST['email']
            ], 'id = ?', [$userId]);

            // Update Module Access
            // First delete existing access
            $db->delete('user_module_access', 'user_id = ?', [$userId]);

            // Insert new access
            if (!empty($_POST['modules'])) {
                foreach ($_POST['modules'] as $module) {
                    $db->insert('user_module_access', [
                        'user_id' => $userId,
                        'module' => $module
                    ]);
                }
            }
            
            $db->commit();
            $success = "User details and permissions updated successfully.";
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Error updating user: " . $e->getMessage();
        }
    }

    // Fetch User Details
    $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

    if (!$user) {
        echo "User not found.";
        exit;
    }

    // Fetch User Permissions
    $userPermissions = $db->fetchAll("SELECT module FROM user_module_access WHERE user_id = ?", [$userId]);
    $currentModules = array_column($userPermissions, 'module');

    // Define available modules
    $availableModules = [
        'inventory' => 'Inventory',
        'sales' => 'Sales',
        'purchases' => 'Purchases',
        'accounting' => 'Accounting',
        'reports' => 'Reports',
        'hrm' => 'HRM',
        'crm' => 'CRM'
    ];
    ?>

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

    <div class="card">
        <div class="card-header">
            <div class="card-title">Edit User: <?php echo htmlspecialchars($user['full_name']); ?></div>
            <a href="users.php" class="btn btn-sm btn-secondary">Back to List</a>
        </div>
        <div style="padding: 20px;">
            <form method="POST">
                <input type="hidden" name="update_user" value="1">
                <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled title="Username cannot be changed">
                    </div>
                </div>
                <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <!-- Phone column does not exist in users table
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        -->
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: 600;">Module Access</label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                        <?php foreach ($availableModules as $key => $label): ?>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="modules[]" value="<?php echo $key; ?>" 
                                    style="width: 16px; height: 16px;"
                                    <?php echo in_array($key, $currentModules) ? 'checked' : ''; ?>>
                                <span><?php echo $label; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

</div> <!-- End content-area -->
</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>
