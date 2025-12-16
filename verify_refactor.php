<?php
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/Subscription.php';
require_once 'classes/Auth.php';

$db = Database::getInstance();
$subscription = new Subscription();
$auth = new Auth();

echo "<h1>Verification Results</h1>";

// 1. Check Subscriptions Table Structure
echo "<h2>1. Subscriptions Table Structure</h2>";
$columns = $db->fetchAll("DESCRIBE subscriptions");
$hasCompanyId = false;
foreach ($columns as $col) {
    if ($col['Field'] === 'company_id') {
        $hasCompanyId = true;
        echo "✅ company_id column exists.<br>";
        break;
    }
}
if (!$hasCompanyId) {
    echo "❌ company_id column MISSING!<br>";
}

// 2. Check Data Population
echo "<h2>2. Data Population</h2>";
$orphans = $db->fetchOne("SELECT COUNT(*) as count FROM subscriptions WHERE company_id IS NULL");
if ($orphans['count'] == 0) {
    echo "✅ All subscriptions have a company_id.<br>";
} else {
    echo "❌ Found {$orphans['count']} subscriptions with NULL company_id.<br>";
}

// 3. Test Subscription Logic
echo "<h2>3. Logic Test</h2>";
// Find a user with a subscription
$sub = $db->fetchOne("SELECT * FROM subscriptions LIMIT 1");
if ($sub) {
    $companyId = $sub['company_id'];
    echo "Testing with Company ID: $companyId<br>";

    // Test getSubscription
    $fetchedSub = $subscription->getSubscription($companyId);
    if ($fetchedSub && $fetchedSub['id'] == $sub['id']) {
        echo "✅ getSubscription($companyId) works.<br>";
    } else {
        echo "❌ getSubscription failed.<br>";
    }

    // Test hasActiveSubscription
    // Manually set status to active for test if needed, but let's just check the result matches manual expectation
    $isActive = $subscription->hasActiveSubscription($companyId);
    echo "hasActiveSubscription($companyId) returned: " . ($isActive ? 'true' : 'false') . "<br>";
    echo "Actual status in DB: " . $sub['status'] . "<br>";
} else {
    echo "⚠️ No subscriptions found to test.<br>";
}

echo "<h2>4. Admin Queries Syntax Check</h2>";
// We can't easily run the full admin page logic without session, but we can try to prepare the queries
try {
    // Users Query from modules/admin/users.php
    $usersQuery = "
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
    ";
    $db->query($usersQuery); // Just preparing/executing to check for syntax errors
    echo "✅ Admin Users Query syntax is valid.<br>";

    // Companies Query from modules/admin/companies.php
    $companiesQuery = "
        SELECT 
            c.id, 
            c.company_name, 
            c.created_at,
            (SELECT COUNT(*) FROM users u WHERE u.company_id = c.id) as user_count,
            (SELECT full_name FROM users u WHERE u.company_id = c.id ORDER BY u.created_at ASC LIMIT 1) as owner_name,
            (SELECT email FROM users u WHERE u.company_id = c.id ORDER BY u.created_at ASC LIMIT 1) as owner_email,
            s.plan_name,
            s.status as subscription_status,
            s.trial_ends_at
        FROM company_settings c
        LEFT JOIN subscriptions s ON c.id = s.company_id AND s.id = (
            SELECT MAX(id) FROM subscriptions WHERE company_id = c.id
        )
        ORDER BY c.created_at DESC
    ";
    $db->query($companiesQuery);
    echo "✅ Admin Companies Query syntax is valid.<br>";

} catch (Exception $e) {
    echo "❌ Query Syntax Error: " . $e->getMessage() . "<br>";
}
