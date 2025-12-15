<?php
// modules/admin/permissions.php
$pageTitle = 'Manage Permissions';
$currentPage = 'roles';
require_once '../../config/config.php';
require_once '../../includes/admin_layout.php';

// Auth::enforceGlobalRouteSecurity() handles permissions.
$db = Database::getInstance();
$roleId = $_GET['role_id'] ?? null;

if (!$roleId) {
    header('Location: roles.php');
    exit;
}

$role = $db->fetchOne("SELECT * FROM roles WHERE id = ?", [$roleId]);
if (!$role) die("Role not found");

// Handle Save
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        $db->delete('role_permissions', 'role_id = ?', [$roleId]);
        
        if (!empty($_POST['permissions'])) {
            foreach ($_POST['permissions'] as $permId) {
                $db->insert('role_permissions', [
                    'role_id' => $roleId,
                    'permission_id' => $permId
                ]);
            }
        }
        $db->commit();
        $success = "Permissions updated successfully.";
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

// Fetch Data
$allPerms = $db->fetchAll("SELECT * FROM permissions ORDER BY module, action");
$groupedPerms = [];
foreach ($allPerms as $p) {
    $groupedPerms[$p['module']][] = $p;
}

$assigned = $db->fetchAll("SELECT permission_id FROM role_permissions WHERE role_id = ?", [$roleId]);
$assignedIds = array_column($assigned, 'permission_id');
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">Permissions for: <?php echo htmlspecialchars($role['name']); ?></div>
        <a href="roles.php" class="btn btn-sm btn-secondary">Back to Roles</a>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success" style="margin: 20px;"><i class="fas fa-check"></i> <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" style="margin: 20px;"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <div style="padding: 20px;">
        <form method="POST">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php foreach ($groupedPerms as $module => $perms): ?>
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff;">
                        <div style="background: #f8fafc; padding: 10px 15px; font-weight: 600; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between;">
                            <span><?php echo ucfirst($module); ?></span>
                            <small style="cursor: pointer; color: #3b82f6;" onclick="toggleModule(this)">Select All</small>
                        </div>
                        <div style="padding: 15px;">
                            <?php foreach ($perms as $perm): ?>
                                <div style="margin-bottom: 8px;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="checkbox" name="permissions[]" value="<?php echo $perm['id']; ?>"
                                               <?php echo in_array($perm['id'], $assignedIds) ? 'checked' : ''; ?>>
                                        <span><?php echo ucfirst($perm['action']); ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModule(el) {
        const container = el.closest('div').nextElementSibling;
        const checkboxes = container.querySelectorAll('input[type="checkbox"]');
        const allChecked = Array.from(checkboxes).every(c => c.checked);
        checkboxes.forEach(c => c.checked = !allChecked);
    }
</script>

</div> <!-- End content-area -->
</main>
</div>
</body>
</html>
