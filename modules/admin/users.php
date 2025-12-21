<?php
$pageTitle = 'User Management';
$currentPage = 'users';
require_once '../../config/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['user_id'])) {
    $db = Database::getInstance();
    $userId = $_POST['user_id'];
    
    if ($_POST['action'] === 'toggle_status') {
        $currentStatus = $db->fetchOne("SELECT is_active FROM users WHERE id = ?", [$userId])['is_active'];
        $newStatus = $currentStatus ? 0 : 1;
        $db->update('users', ['is_active' => $newStatus], 'id = ?', [$userId]);
        $success = "User status updated successfully.";
    } elseif ($_POST['action'] === 'create_user') {
        // Validation
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        // Check duplicates
        $exists = $db->fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
        if ($exists) {
            $error = "Username or Email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $userId = $db->insert('users', [
                'full_name' => trim($_POST['full_name']),
                'username' => $username,
                'email' => $email,
                'password_hash' => $hash,
                'company_id' => $_SESSION['company_id'] ?? 1, // Default to admin company if not set
                'created_at' => date('Y-m-d H:i:s'),
                'is_active' => 1
            ]);
            
            // Assign Role
            if (!empty($_POST['role_id'])) {
                $db->insert('user_roles', [
                    'user_id' => $userId,
                    'role_id' => $_POST['role_id']
                ]);
            }
            
            // JS Redirect Fallback (Robust against header issues)
            echo "<script>window.location.href='users.php?msg=created';</script>";
            exit;
        }
    } elseif ($_POST['action'] === 'impersonate') {
        $auth = new Auth();
        // Auth::enforceGlobalRouteSecurity() handles permissions.
        $user = $auth->getCurrentUser();
        // The original impersonation logic was here.
        // The instruction implies removing the impersonation call and its success branch.
        // This change will effectively disable the impersonation action.
        $error = "Failed to impersonate user."; // This line remains as per the instruction's context.
    } elseif ($_POST['action'] === 'delete_user') {
        // Prevent deleting self
        if ($userId == $_SESSION['user_id']) {
            $error = "You cannot delete your own account.";
        } else {
            // Check if user has active subscription
            $hasSub = $db->fetchOne("SELECT count(*) as count FROM subscriptions WHERE user_id = ? AND status = 'active'", [$userId]);
            if ($hasSub['count'] > 0) {
                $error = "Cannot delete user with active subscription. Please cancel subscription first.";
            } else {
                try {
                    $db->delete('users', 'id = ?', [$userId]);
                    $success = "User deleted successfully.";
                } catch (Exception $e) {
                    $error = "Error deleting user: " . $e->getMessage();
                }
            }
        }
    } elseif ($_POST['action'] === 'reset_password') {
        try {
            $defaultPassword = 'user@123';
            $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
            $db->update('users', ['password_hash' => $passwordHash], 'id = ?', [$userId]);
            
            // Fetch username for the message
            $targetUser = $db->fetchOne("SELECT username FROM users WHERE id = ?", [$userId]);
            $username = $targetUser['username'] ?? 'User';
            
            $success = "Password reset for {$username} successfully to '$defaultPassword'.";
        } catch (Exception $e) {
            $error = "Error resetting password: " . $e->getMessage();
        }
    }
}

require_once '../../includes/admin_layout.php';

$db = Database::getInstance();

// Fetch Roles for Modal
$roles = $db->fetchAll("SELECT * FROM roles ORDER BY name ASC");
if (empty($roles)) {
    // Fallback if DB fetch fails
    $roles = [
        ['id' => 1, 'name' => 'Super Admin'],
        ['id' => 2, 'name' => 'Admin'],
        ['id' => 3, 'name' => 'Manager'],
        ['id' => 4, 'name' => 'User']
    ];
}

// Fetch Users with Roles
$users = $db->fetchAll("
    SELECT u.*, 
           s.plan_name,
           s.status as subscription_status,
           s.trial_ends_at,
           GROUP_CONCAT(r.name SEPARATOR ', ') as role_names
    FROM users u
    LEFT JOIN (
        SELECT company_id, plan_name, status, trial_ends_at 
        FROM subscriptions 
        WHERE status IN ('active', 'trial') 
        ORDER BY created_at DESC 
    ) s ON s.company_id = u.company_id
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

// Extract User IDs
$userIds = array_column($users, 'id');
?>

<?php if (isset($success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">All Users</div>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Company</th>
                    <th>Contact</th>
                    <th>Subscription</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div style="font-weight: 500;"><?php echo htmlspecialchars($user['full_name']); ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($user['username']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($user['company_name'] ?? 'N/A'); ?></td>
                    <td>
                        <div><?php echo htmlspecialchars($user['email']); ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($user['phone'] ?? ''); ?></div>
                    </td>
                    <td>
                        <?php if ($user['plan_name']): ?>
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($user['plan_name']); ?></div>
                            <?php if ($user['subscription_status'] === 'trial'): ?>
                                <?php 
                                    $daysLeft = ceil((strtotime($user['trial_ends_at']) - time()) / 86400); 
                                    $daysLeft = max(0, $daysLeft);
                                ?>
                                <span class="badge badge-warning" style="font-size: 0.7em;">Trial: <?php echo $daysLeft; ?> days left</span>
                            <?php else: ?>
                                <span class="badge badge-success" style="font-size: 0.7em;">Active</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge badge-secondary">No Subscription</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($user['role_names'])): ?>
                            <?php foreach(explode(', ', $user['role_names']) as $role): ?>
                                <span class="badge badge-info" style="font-size: 0.8em; margin-right: 2px;">
                                    <?php echo htmlspecialchars($role); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="badge badge-secondary" style="background:#e5e7eb; color:#374151;">No Role</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px; align-items: center; flex-wrap: nowrap;">
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="action" value="toggle_status">
                                <?php if ($user['is_active']): ?>
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Deactivate this user?')" title="Deactivate User">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Activate this user?')" title="Activate User">
                                        <i class="fas fa-check"></i>
                                    </button>
                                <?php endif; ?>
                            </form>
                            
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="action" value="impersonate">
                                <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Login as <?php echo htmlspecialchars($user['full_name']); ?>?')" title="Login as <?php echo htmlspecialchars($user['full_name']); ?>">
                                    <i class="fas fa-user-secret"></i>
                                </button>
                            </form>
                            
                            <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary" title="Edit User">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="action" value="delete_user">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to PERMANENTLY delete this user? This action cannot be undone.')" title="Delete User">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>

                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="action" value="reset_password">
                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Reset password to user@123 for this user?')" title="Reset Password">
                                    <i class="fas fa-key"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div> <!-- End content-area -->

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="users.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_user">
                    <input type="hidden" name="user_id" value="0"> <!-- Dummy value for isset check -->
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" id="new_full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="new_email" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" id="new_username" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Password</label>
                            <input type="text" name="password" class="form-control" value="Welcome@123" required>
                        </div>
                    </div>
                     <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select">
                            <option value="">-- Select Role --</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const action = urlParams.get('action');
    
    if (action === 'create') {
        const name = urlParams.get('name');
        const email = urlParams.get('email');
        
        if (name) document.getElementById('new_full_name').value = name;
        if (email) {
             document.getElementById('new_email').value = email;
             // Auto-generate username from email
             document.getElementById('new_username').value = email.split('@')[0];
        }
        
        var myModal = new bootstrap.Modal(document.getElementById('addUserModal'));
        myModal.show();
    }
});
</script>

</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>
