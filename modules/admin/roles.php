<?php
// modules/admin/roles.php
$pageTitle = 'Role Management';
$currentPage = 'roles';
require_once '../../config/config.php';
require_once '../../includes/admin_layout.php';

$db = Database::getInstance();

// Handle Actions (Create/Edit/Delete System Roles)
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create_role') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            if (empty($name)) throw new Exception("Role name is required");
            
            // Check existence
            $exists = $db->fetchOne("SELECT id FROM roles WHERE name = ?", [$name]);
            if ($exists) throw new Exception("Role already exists");
            
            $db->insert('roles', [
                'name' => $name,
                'description' => $description,
                'company_id' => NULL // System Role
            ]);
            $success = "System role created successfully";
            
        } elseif ($action === 'delete_role') {
            $roleId = $_POST['role_id'];
            // Check usage
            $usage = $db->fetchOne("SELECT COUNT(*) as count FROM user_roles WHERE role_id = ?", [$roleId]);
            if ($usage['count'] > 0) throw new Exception("Cannot delete role: Assigned to {$usage['count']} users.");
            
            $db->delete('roles', 'id = ?', [$roleId]);
            $success = "Role deleted successfully";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch All Roles
$roles = $db->fetchAll("SELECT * FROM roles ORDER BY name ASC");

?>

<div class="card">
    <div class="card-header">
        <div class="card-title">System Roles</div>
        <button class="btn btn-primary btn-sm" onclick="openModal('addRoleModal')">
            <i class="fas fa-plus"></i> Add New Role
        </button>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success" style="margin: 20px;"><i class="fas fa-check"></i> <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" style="margin: 20px;"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Role Name</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td>
                            <span style="font-weight: 600;"><?php echo htmlspecialchars($role['name']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($role['description']); ?></td>
                        <td>
                            <?php if (empty($role['company_id'])): ?>
                                <span class="badge badge-info">System</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Custom (Company <?php echo $role['company_id']; ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="permissions.php?role_id=<?php echo $role['id']; ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-key"></i> Permissions
                            </a>
                            <?php if (empty($role['company_id'])): // Only delete system roles here ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteRole(<?php echo $role['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Role Modal -->
<div id="addRoleModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div class="modal-content" style="background:white; padding:30px; border-radius:8px; width:400px; max-width:90%;">
        <h3>Add System Role</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create_role">
            <div class="form-group" style="margin-bottom:15px;">
                <label>Role Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label>Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <div style="text-align:right;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addRoleModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Role</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_role">
    <input type="hidden" name="role_id" id="deleteRoleId">
</form>

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }
    function deleteRole(id) {
        if(confirm('Are you sure?')) {
            document.getElementById('deleteRoleId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

</div> <!-- End content-area -->
</main>
</div>
</body>
</html>
