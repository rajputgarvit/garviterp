<?php
// modules/settings/roles.php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Database.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getCurrentUser();
$db = Database::getInstance();

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
            
            // Check if name exists for this company or system
            $exists = $db->fetchOne("SELECT id FROM roles WHERE name = ? AND (company_id IS NULL OR company_id = ?)", [$name, $user['company_id']]);
            if ($exists) throw new Exception("Role with this name already exists");
            
            $db->insert('roles', [
                'name' => $name,
                'description' => $description,
                'company_id' => $user['company_id']
            ]);
            
            $success = "Role created successfully";
            
        } elseif ($action === 'edit_role') {
            $roleId = $_POST['role_id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            
            // Verify ownership
            $role = $db->fetchOne("SELECT * FROM roles WHERE id = ? AND company_id = ?", [$roleId, $user['company_id']]);
            if (!$role) throw new Exception("Invalid role or permission denied");
            
            $db->update('roles', [
                'name' => $name,
                'description' => $description
            ], 'id = ?', [$roleId]);
            
            $success = "Role updated successfully";
            
        } elseif ($action === 'delete_role') {
            $roleId = $_POST['role_id'];
            
            // Verify ownership
            $role = $db->fetchOne("SELECT * FROM roles WHERE id = ? AND company_id = ?", [$roleId, $user['company_id']]);
            if (!$role) throw new Exception("Invalid role or permission denied");
            
            // Check if assigned to users
            $usage = $db->fetchOne("SELECT COUNT(*) as count FROM user_roles WHERE role_id = ?", [$roleId]);
            if ($usage['count'] > 0) throw new Exception("Cannot delete role: It is assigned to " . $usage['count'] . " users.");
            
            $db->query("DELETE FROM roles WHERE id = ?", [$roleId]);
            $success = "Role deleted successfully";
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch Roles (System + Company)
$roles = $db->fetchAll("SELECT * FROM roles WHERE company_id IS NULL OR company_id = ? ORDER BY company_id ASC, name ASC", [$user['company_id']]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Management - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .role-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            transition: all 0.2s;
        }
        .role-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .role-info h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #1e293b;
        }
        .role-info p {
            margin: 0;
            font-size: 14px;
            color: #64748b;
        }
        .role-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .badge-system {
            background: #e0f2fe;
            color: #0369a1;
        }
        .badge-custom {
            background: #f0fdf4;
            color: #15803d;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include INCLUDES_PATH . '/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include INCLUDES_PATH . '/header.php'; ?>
            
            <div class="content-area">
                <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1><i class="fas fa-user-shield"></i> Role Management</h1>
                        <p>Define roles and permissions for your team members</p>
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
                
                <div class="roles-list">
                    <?php foreach ($roles as $role): ?>
                        <div class="role-card">
                            <div class="role-info">
                                <div style="display: flex; align-items: center;">
                                    <h3><?php echo htmlspecialchars($role['name']); ?></h3>
                                    <?php if (empty($role['company_id'])): ?>
                                        <span class="role-badge badge-system">System Default</span>
                                    <?php else: ?>
                                        <span class="role-badge badge-custom">Custom Role</span>
                                    <?php endif; ?>
                                </div>
                                <p><?php echo htmlspecialchars($role['description']); ?></p>
                            </div>
                            <div class="role-actions">
                                <?php if (!empty($role['company_id'])): ?>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editRole(<?php echo htmlspecialchars(json_encode($role)); ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteRole(<?php echo $role['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled title="System roles cannot be edited">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-secondary" onclick="window.location.href='permissions.php?role_id=<?php echo $role['id']; ?>'" title="Manage Permissions">
                                    <i class="fas fa-key"></i> Permissions
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Role Modal -->
    <div id="addRoleModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Create New Role</h2>
            <form method="POST">
                <input type="hidden" name="action" id="formAction" value="create_role">
                <input type="hidden" name="role_id" id="roleId">
                
                <div class="form-group">
                    <label>Role Name</label>
                    <input type="text" name="name" id="roleName" class="form-control" required placeholder="e.g. Sales Manager">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="roleDescription" class="form-control" rows="3" placeholder="Describe the role's responsibilities"></textarea>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeModal('addRoleModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Role</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_role">
        <input type="hidden" name="role_id" id="deleteRoleId">
    </form>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
            if (id === 'addRoleModal') {
                document.getElementById('modalTitle').textContent = 'Create New Role';
                document.getElementById('formAction').value = 'create_role';
                document.getElementById('roleName').value = '';
                document.getElementById('roleDescription').value = '';
            }
        }
        
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        
        function editRole(role) {
            openModal('addRoleModal');
            document.getElementById('modalTitle').textContent = 'Edit Role';
            document.getElementById('formAction').value = 'edit_role';
            document.getElementById('roleId').value = role.id;
            document.getElementById('roleName').value = role.name;
            document.getElementById('roleDescription').value = role.description;
        }
        
        function deleteRole(id) {
            if(confirm('Are you sure you want to delete this role?')) {
                document.getElementById('deleteRoleId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
        
        function managePermissions(id) {
            alert('Permission management is coming in the next update.');
        }
    </script>
</body>
</html>
