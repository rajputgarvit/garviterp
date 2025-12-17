<?php
$pageTitle = 'Edit Permissions';
$currentPage = 'roles';
require_once '../../config/config.php';
require_once '../../includes/header.php';
require_once '../../classes/Permission.php';

$auth = new Auth();
$user = $auth->getCurrentUser();
if (!$user) exit;

$roleId = $_GET['id'] ?? null;
if (!$roleId) {
    header('Location: roles.php');
    exit;
}

$perm = new Permission();
$companyId = $user['company_id'];
$role = $perm->getRole($roleId, $companyId);

// If role not found (maybe system role?), check generic fetch if needed, 
// but for editing permissions we restrict to company roles usually.
// If allowing system role edit (by super admin?), need logic.
// Assuming only company roles for now.
if (!$role) {
    // Check if it's a system role and maybe allow viewing?
    // For now, redirect.
    header('Location: roles.php?error=Role not found');
    exit;
}

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedPermissions = $_POST['permissions'] ?? [];
    if ($perm->assignPermissionsToRole($roleId, $companyId, $selectedPermissions)) {
        $success = "Permissions updated successfully.";
    } else {
        $error = "Failed to update permissions.";
    }
}

// Fetch Data
$allPermissions = $perm->getPermissionsByModule();
$rolePermissions = $perm->getRolePermissions($roleId);

?>

<div class="container-fluid" style="padding: 20px;">
    <div class="mb-4">
        <a href="roles.php" class="text-muted"><i class="fas fa-arrow-left"></i> Back to Roles</a>
        <h2 class="mt-2">Permissions for <span class="text-primary"><?php echo htmlspecialchars($role['name']); ?></span></h2>
        <p class="text-muted"><?php echo htmlspecialchars($role['description']); ?></p>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><i class="fas fa-check"></i> <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="card mb-4" style="position: sticky; top: 20px; z-index: 100;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-bold fs-5">Manage Access</span>
                    <span class="text-muted ms-2">Select the capabilities for this role.</span>
                </div>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>

        <div class="row">
            <?php foreach ($allPermissions as $module => $actions): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-light fw-bold text-uppercase d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars(ucfirst($module)); ?>
                            <div class="form-check">
                                <input class="form-check-input select-all-module" type="checkbox" data-module="<?php echo $module; ?>" title="Select All">
                            </div>
                        </div>
                        <div class="card-body">
                            <?php foreach ($actions as $p): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input permission-checkbox module-<?php echo $module; ?>" 
                                           type="checkbox" 
                                           name="permissions[]" 
                                           value="<?php echo $p['id']; ?>" 
                                           id="perm_<?php echo $p['id']; ?>"
                                           <?php echo in_array($p['id'], $rolePermissions) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="perm_<?php echo $p['id']; ?>" title="<?php echo htmlspecialchars($p['description']); ?>">
                                        <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($p['action']))); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-4 text-end">
             <button type="submit" class="btn btn-primary px-4 btn-lg">
                <i class="fas fa-save"></i> Save Permissions
            </button>
        </div>
    </form>
</div>

<script>
    document.querySelectorAll('.select-all-module').forEach(cb => {
        cb.addEventListener('change', function() {
            const module = this.dataset.module;
            const checks = document.querySelectorAll('.module-' + module);
            checks.forEach(c => c.checked = this.checked);
        });
    });
</script>

<?php require_once '../../includes/footer.php'; ?>
