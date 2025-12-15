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
            $db->delete('user_module_access', 'user_id = ?', [$userId]);
            if (!empty($_POST['modules'])) {
                foreach ($_POST['modules'] as $module) {
                    $db->insert('user_module_access', [
                        'user_id' => $userId,
                        'module' => $module
                    ]);
                }
            }

            // Update Roles
            $db->delete('user_roles', 'user_id = ?', [$userId]);
            if (!empty($_POST['roles'])) {
                foreach ($_POST['roles'] as $roleId) {
                    $res = $db->insert('user_roles', [
                        'user_id' => $userId,
                        'role_id' => $roleId
                    ]);
                }
            }
            
            $db->commit();
            $success = "User details, roles, and permissions updated successfully.";
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

    // Fetch User Permissions (legacy module access)
    $userPermissions = $db->fetchAll("SELECT module FROM user_module_access WHERE user_id = ?", [$userId]);
    $currentModules = array_column($userPermissions, 'module');

    // Fetch Assigned Roles for this user
    $userRoles = $db->fetchAll("
        SELECT r.id, r.name 
        FROM user_roles ur 
        JOIN roles r ON ur.role_id = r.id 
        WHERE ur.user_id = ?
    ", [$userId]);
    $assignedRoleIds = array_column($userRoles, 'id');
    $roleNames = array_column($userRoles, 'name');

    // Fetch All Available Roles
    $allRoles = $db->fetchAll("SELECT * FROM roles ORDER BY name ASC");

    // Fetch Computed RBAC Permissions
    $rbacPermissions = $db->fetchAll("
        SELECT DISTINCT p.module, p.action
        FROM user_roles ur
        JOIN role_permissions rp ON ur.role_id = rp.role_id
        JOIN permissions p ON rp.permission_id = p.id
        WHERE ur.user_id = ?
        ORDER BY p.module, p.action
    ", [$userId]);

    $displayPermissions = [];
    foreach ($rbacPermissions as $perm) {
        $displayPermissions[$perm['module']][] = $perm['action'];
    }

    // Define available legacy modules
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
                <!-- Name and Email Row -->
                <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: 600;">Assign Roles</label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; padding: 15px; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px;">
                        <?php foreach ($allRoles as $role): ?>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 5px; border: 1px solid #eee; border-radius: 4px;">
                                <input type="checkbox" name="roles[]" value="<?php echo $role['id']; ?>" 
                                    style="width: 16px; height: 16px;"
                                    <?php echo in_array($role['id'], $assignedRoleIds) ? 'checked' : ''; ?>>
                                <span><?php echo htmlspecialchars($role['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: 600;">Legacy Module Access (Sidebar Visibility)</label>
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

    <!-- Effective Permissions Display -->
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <div class="card-title">Effective Permissions</div>
        </div>
        <div style="padding: 20px;">
            <div style="margin-bottom: 20px;">
                <label style="font-weight: 600; color: #64748b;">Assigned Roles:</label>
                <div style="margin-top: 5px;">
                    <?php if (!empty($roleNames)): ?>
                        <?php foreach($roleNames as $role): ?>
                            <span class="badge badge-info"><?php echo htmlspecialchars($role); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted">No roles assigned</span>
                    <?php endif; ?>
                </div>
            </div>

            <label style="font-weight: 600; color: #64748b; display: block; margin-bottom: 10px;">Computed Access Rights:</label>
            <?php if (in_array('Super Admin', $roleNames)): ?>
                 <div class="alert alert-success">User has <strong>Super Admin</strong> role (Full Access)</div>
            <?php elseif (!empty($displayPermissions)): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
                    <?php foreach ($displayPermissions as $module => $actions): ?>
                        <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; background: #fff;">
                            <div style="font-weight: 600; border-bottom: 1px solid #f1f5f9; padding-bottom: 5px; margin-bottom: 5px;">
                                <?php echo ucfirst($module); ?>
                            </div>
                            <div style="font-size: 0.9em; color: #475569;">
                                <?php foreach($actions as $action): ?>
                                    <span style="display: inline-block; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; margin: 2px;">
                                        <?php echo ucfirst($action); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">No specific permissions granted via roles.</p>
            <?php endif; ?>
        </div>
    </div>

</div> <!-- End content-area -->
</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>
