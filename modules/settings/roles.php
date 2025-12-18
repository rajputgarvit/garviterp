<?php
$currentPage = 'roles';
require_once '../../config/config.php';
require_once '../../includes/admin_layout.php'; // Includes sidebar + header + wrappers
require_once '../../classes/Permission.php';

// Map admin_layout user to local var expected by logic
$user = $currentUser;
?>

<div>

<?php
$perm = new Permission();
$companyId = $user['company_id'];
$success = '';
$error = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create_role') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (empty($name)) throw new Exception("Role name is required");
            
            // Check existence
            $existing = $perm->getRoles($companyId);
            foreach ($existing as $r) {
                if (strcasecmp($r['name'], $name) === 0) throw new Exception("Role with this name already exists");
            }
            
            $perm->createRole($companyId, $name, $description);
            $success = "Role created successfully";
            
        } elseif ($action === 'edit_role') {
            $roleId = $_POST['role_id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            
            $perm->updateRole($roleId, $companyId, $name, $description);
            $success = "Role updated successfully";
            
        } elseif ($action === 'delete_role') {
            $roleId = $_POST['role_id'];
            $perm->deleteRole($roleId, $companyId);
            $success = "Role deleted successfully";
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$roles = $perm->getRoles($companyId);
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-user-shield"></i> Role Management</h1>
            <p class="text-muted">Define roles and permissions for your team members</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('addRoleModal')">
            <i class="fas fa-plus"></i> Add New Role
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check"></i> <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($roles as $role): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($role['name']); ?></h5>
                            <?php if (empty($role['company_id'])): ?>
                                <span class="badge bg-info text-dark">System Default</span>
                            <?php else: ?>
                                <span class="badge bg-success">Custom Role</span>
                            <?php endif; ?>
                        </div>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($role['description']); ?></p>
                        <p class="card-text"><small class="text-muted">Created: <?php echo date('M d, Y', strtotime($role['created_at'])); ?></small></p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 d-flex justify-content-between">
                        <div>
                            <?php if (!empty($role['company_id'])): ?>
                                <button class="btn btn-sm btn-outline-secondary" onclick="editRole(<?php echo htmlspecialchars(json_encode($role)); ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteRole(<?php echo $role['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary" disabled title="System roles cannot be edited">
                                    <i class="fas fa-lock"></i> Edit
                                </button>
                            <?php endif; ?>
                        </div>
                        <a href="role_permissions.php?id=<?php echo $role['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-key"></i> Permissions
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add/Edit Role Modal -->
<div id="addRoleModal" class="modal" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Create New Role</h5>
                <button type="button" class="btn-close" onclick="closeModal('addRoleModal')"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create_role">
                    <input type="hidden" name="role_id" id="roleId">
                    
                    <div class="mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text" name="name" id="roleName" class="form-control" required placeholder="e.g. Sales Manager">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="roleDescription" class="form-control" rows="3" placeholder="Describe the role's responsibilities"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="../admin/settings.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back to Settings</a>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addRoleModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_role">
    <input type="hidden" name="role_id" id="deleteRoleId">
</form>

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'block';
        document.getElementById(id).classList.add('show');
        if (id === 'addRoleModal') {
            // Reset for add
            if (document.getElementById('formAction').value === 'create_role') {
                document.getElementById('modalTitle').textContent = 'Create New Role';
                document.getElementById('roleName').value = '';
                document.getElementById('roleDescription').value = '';
            }
        }
    }
    
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
        document.getElementById(id).classList.remove('show');
        // Reset action to create by default when closing
        setTimeout(() => {
            document.getElementById('formAction').value = 'create_role';
        }, 200);
    }
    
    function editRole(role) {
        document.getElementById('modalTitle').textContent = 'Edit Role';
        document.getElementById('formAction').value = 'edit_role';
        document.getElementById('roleId').value = role.id;
        document.getElementById('roleName').value = role.name;
        document.getElementById('roleDescription').value = role.description;
        openModal('addRoleModal');
    }
    
    function deleteRole(id) {
        if(confirm('Are you sure you want to delete this role?')) {
            document.getElementById('deleteRoleId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

</div><!-- End content-area -->
</main><!-- End main-content -->
</div><!-- End dashboard-wrapper -->

<?php require_once '../../includes/footer.php'; ?>
