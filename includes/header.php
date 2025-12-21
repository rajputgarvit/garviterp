<?php
// Check if this is an SPA request
$isSpaRequest = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';

if (!$isSpaRequest):
    // Fetch Active Broadcasts
    if (!class_exists('Broadcast')) {
        // Adjust path if needed, assuming header is included from a module depth of 2 usually
        // But better to use __DIR__ if possible or defined constants. Constants defined in config.php are best.
        // CLASSES_PATH is likely defined.
        if (defined('CLASSES_PATH')) {
            require_once CLASSES_PATH . '/Broadcast.php';
        } elseif (file_exists(__DIR__ . '/../classes/Broadcast.php')) {
            require_once __DIR__ . '/../classes/Broadcast.php';
        }
    }
    
    $activeBroadcasts = [];
    if (class_exists('Broadcast')) {
        $broadcastSystem = new Broadcast();
        $activeBroadcasts = $broadcastSystem->getActiveBroadcasts();
    }
?>
<?php if (!empty($activeBroadcasts)): ?>
    <?php foreach ($activeBroadcasts as $broadcast): ?>
        <div class="alert alert-<?php echo htmlspecialchars($broadcast['type']); ?>" style="margin: 0; border-radius: 0; text-align: center; position: relative; z-index: 1002;">
            <strong><?php echo htmlspecialchars($broadcast['title']); ?>:</strong> 
            <?php echo htmlspecialchars($broadcast['message']); ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php if (isset($_SESSION['is_impersonating']) && $_SESSION['is_impersonating']): ?>
    <div style="background-color: #ff4757; color: white; padding: 10px; text-align: center; width: 100%; position: sticky; top: 0; z-index: 1001; height: 50px; display: flex; align-items: center; justify-content: center;">
        You are currently impersonating &nbsp; <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>.
        <a href="<?php echo MODULES_URL; ?>/auth/stop-impersonation.php" style="color: white; text-decoration: underline; font-weight: bold; margin-left: 10px;">
            Exit Impersonation
        </a>
    </div>
<?php endif; ?>

<div class="page-title-bar" style="margin-top: 20px; padding: 0 2rem; display: flex; align-items: center; justify-content: space-between;">
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0;">
        <?php echo ucwords(str_replace(['-', '_'], [' ', ' '], basename(dirname($_SERVER['PHP_SELF'])))); ?>
    </h1>
</div>

<?php
// Subscription Banner Logic
if (isset($user['company_id'])) {
    if (!class_exists('Subscription')) {
        require_once CLASSES_PATH . '/Subscription.php';
    }
    $subscriptionHeader = new Subscription();
    $subStatsHeader = $subscriptionHeader->getSubscriptionStats($user['company_id']);
    
    if ($subStatsHeader && $subStatsHeader['is_trial']):
        $daysLeft = $subStatsHeader['days_remaining'];
        $bannerClass = $daysLeft <= 3 ? 'alert-danger' : 'alert-info';
?>
    <div class="alert <?php echo $bannerClass; ?>" style="margin: 10px 20px 0 20px; display: flex; justify-content: space-between; align-items: center; border-radius: 8px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-clock" style="font-size: 1.2em;"></i>
            <div>
                <strong>Free Trial Active</strong>
                <div style="font-size: 0.9em;">You have <?php echo $daysLeft; ?> days remaining in your trial.</div>
            </div>
        </div>
        <a href="<?php echo MODULES_URL; ?>/subscription/select-plan.php" class="btn btn-sm btn-primary" style="white-space: nowrap;">Upgrade Now</a>
    </div>
<?php endif; 
} 
?>
<?php endif; ?>
