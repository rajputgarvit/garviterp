<?php
// Check if this is an SPA request
$isSpaRequest = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';

if (!$isSpaRequest):
?>
<?php if (isset($_SESSION['is_impersonating']) && $_SESSION['is_impersonating']): ?>
    <div style="background-color: #ff4757; color: white; padding: 10px; text-align: center; width: 100%; position: sticky; top: 0; z-index: 1001; height: 50px; display: flex; align-items: center; justify-content: center;">
        You are currently impersonating <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>.
        <a href="<?php echo MODULES_URL; ?>/auth/stop-impersonation.php" style="color: white; text-decoration: underline; font-weight: bold; margin-left: 10px;">
            Exit Impersonation
        </a>
    </div>
<?php endif; ?>

<header class="top-header" style="<?php echo (isset($_SESSION['is_impersonating']) && $_SESSION['is_impersonating']) ? 'top: 50px;' : ''; ?>">
    <div class="header-left" style="display: flex; align-items: center; gap: 15px;">
        <h1><?php echo ucwords(str_replace(['-', '_'], [' ', ' '], basename(dirname($_SERVER['PHP_SELF'])))); ?></h1>
    </div>
    <div class="header-right">
        <?php if ($auth->hasRole('Super Admin')): ?>
            <a href="<?php echo MODULES_URL; ?>/admin/dashboard.php" class="btn btn-primary btn-sm" style="margin-right: 15px;">
                <i class="fas fa-user-shield"></i> Go to Admin Panel
            </a>
        <?php endif; ?>
        <div class="user-menu">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <div>
                <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <div style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars(is_array($user['roles']) ? implode(', ', $user['roles']) : $user['roles']); ?></div>
            </div>
            <a href="<?php echo MODULES_URL; ?>/auth/logout.php" style="margin-left: 10px; color: var(--danger-color);" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</header>
<?php endif; ?>
