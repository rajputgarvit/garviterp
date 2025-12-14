<?php
// modules/settings/permissions.php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Database.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getCurrentUser();
$db = Database::getInstance();

$roleId = $_GET['role_id'] ?? null;

if (!$roleId) {
    header('Location: roles.php');
    exit;
}

// Fetch Role
$role = $db->fetchOne("SELECT * FROM roles WHERE id = ? AND (company_id IS NULL OR company_id = ?)", [$roleId, $user['company_id']]);

if (!$role) {
    die("Invalid Role or Permission Denied.");
}

// Handle Form Submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $selectedPermissions = $_POST['permissions'] ?? [];
        
        // Begin Transaction
        $db->getConnection()->beginTransaction();
        
        // Clear existing permissions for this role
        // Verify role again to be safe
        $db->query("DELETE FROM role_permissions WHERE role_id = ?", [$roleId]);
        
        // Insert new
        if (!empty($selectedPermissions)) {
            $stmt = $db->getConnection()->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($selectedPermissions as $permId) {
                $stmt->execute([$roleId, $permId]);
            }
        }
        
        $db->getConnection()->commit();
        $success = "Permissions updated successfully.";
        
    } catch (Exception $e) {
        $db->getConnection()->rollBack();
        $error = "Error updating permissions: " . $e->getMessage();
    }
}

// Fetch All Permissions grouped by Module
$allPermissions = $db->fetchAll("SELECT * FROM permissions ORDER BY module, action");
$groupedPermissions = [];
foreach ($allPermissions as $p) {
    $groupedPermissions[$p['module']][] = $p;
}

// Fetch Current Role Permissions
$currentPermsRaw = $db->fetchAll("SELECT permission_id FROM role_permissions WHERE role_id = ?", [$roleId]);
$currentPerms = array_column($currentPermsRaw, 'permission_id');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Permissions - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .permission-group {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .permission-header {
            background: #f8fafc;
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .permission-header h3 {
            margin: 0;
            font-size: 16px;
            color: #334155;
            text-transform: capitalize;
        }
        .permission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            padding: 20px;
        }
        .permission-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .permission-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .permission-item label {
            font-size: 14px;
            color: #475569;
            cursor: pointer;
            text-transform: capitalize;
        }
        .toggle-all {
            font-size: 13px;
            color: #3b82f6;
            cursor: pointer;
            background: none;
            border: none;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include INCLUDES_PATH . '/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include INCLUDES_PATH . '/header.php'; ?>
            
            <div class="content-area">
                <div class="page-header">
                    <a href="roles.php" class="btn btn-outline-secondary btn-sm" style="margin-bottom: 10px; display: inline-block;">
                        <i class="fas fa-arrow-left"></i> Back to Roles
                    </a>
                    <h1>Manage Permissions: <?php echo htmlspecialchars($role['name']); ?></h1>
                    <p>Assign access levels for this role</p>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check"></i> <?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    
                    <?php foreach ($groupedPermissions as $module => $permissions): ?>
                        <div class="permission-group">
                            <div class="permission-header">
                                <h3><i class="fas fa-layer-group"></i> <?php echo ucfirst($module); ?> Module</h3>
                                <button type="button" class="toggle-all" onclick="toggleGroup('<?php echo $module; ?>')">Select All</button>
                            </div>
                            <div class="permission-grid" id="group-<?php echo $module; ?>">
                                <?php foreach ($permissions as $perm): ?>
                                    <div class="permission-item">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="<?php echo $perm['id']; ?>" 
                                               id="perm_<?php echo $perm['id']; ?>"
                                               <?php echo in_array($perm['id'], $currentPerms) ? 'checked' : ''; ?>>
                                        <label for="perm_<?php echo $perm['id']; ?>">
                                            <?php echo ucfirst($perm['action']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="position: sticky; bottom: 20px; background: white; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 -4px 6px -1px rgba(0,0,0,0.1); display: flex; justify-content: flex-end; gap: 10px;">
                        <a href="roles.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Permissions</button>
                    </div>
                    
                </form>
            </div>
        </main>
    </div>

    <script>
        function toggleGroup(module) {
            const group = document.getElementById('group-' + module);
            const checkboxes = group.querySelectorAll('input[type="checkbox"]');
            
            // Check if all are currently checked
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
        }
    </script>
</body>
</html>
