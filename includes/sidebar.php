<?php
// Fetch branding settings if not already available
if (!isset($brandingSettings)) {
    $db = Database::getInstance();
    $brandingSettings = $db->fetchOne("SELECT app_name, logo_path, theme_color FROM company_settings WHERE id = ? LIMIT 1", [$_SESSION['company_id'] ?? 0]);
}
$appName = !empty($brandingSettings['app_name']) ? $brandingSettings['app_name'] : APP_NAME;
$logoPath = !empty($brandingSettings['logo_path']) ? BASE_URL . $brandingSettings['logo_path'] : '';

// Get user info for menu if not available
if (!isset($user) && isset($auth)) {
    $user = $auth->getCurrentUser();
}
?>
<!-- Global Assets -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/style.css?v=<?php echo time(); ?>">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/assets/js/script.js?v=<?php echo time(); ?>"></script>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" style="background-color: #111827 !important; height: 64px;">
    <div class="container-fluid">

        <!-- Menu Items -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/dashboard/') !== false ? 'active' : ''; ?>" href="<?php echo MODULES_URL; ?>/dashboard/index.php">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>

                <!-- Sales -->
                <?php if ($auth->hasModuleAccess('sales') && $auth->hasPermission('sales', 'view')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['PHP_SELF'], '/sales/') !== false ? 'active' : ''; ?>" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-chart-line me-1"></i> Sales
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/dashboard/sales.php">Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/sales/quotations/index.php">Quotations</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/sales/invoices/index.php">Invoices</a></li>
                        <!-- <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/sales/orders/index.php">Sales Orders</a></li> -->
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/sales/reports/index.php">Reports</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/reports/payment-tracking.php">Payment Tracking</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- CRM -->
                <?php if (($auth->hasModuleAccess('sales') || $auth->hasModuleAccess('crm')) && $auth->hasPermission('crm', 'view')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['PHP_SELF'], '/crm/') !== false ? 'active' : ''; ?>" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-users me-1"></i> CRM
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/crm/customers/index.php">Customers</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/crm/leads/index.php">Leads</a></li>
                        <?php if ($auth->hasModuleAccess('purchases')): ?>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/crm/suppliers/index.php">Suppliers</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Inventory -->
                <?php if ($auth->hasModuleAccess('inventory') && $auth->hasPermission('inventory', 'view')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['PHP_SELF'], '/inventory/') !== false ? 'active' : ''; ?>" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-box me-1"></i> Inventory
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/inventory/products/index.php">Products</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/inventory/stock/index.php">Stock Management</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/inventory/warehouses/index.php">Warehouses</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/inventory/settings.php">Settings</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Purchase -->
                <?php if ($auth->hasModuleAccess('purchases') && $auth->hasPermission('purchases', 'view')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['PHP_SELF'], '/purchases/') !== false ? 'active' : ''; ?>" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-shopping-bag me-1"></i> Purchase
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/purchases/orders/index.php">Purchase Orders</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/purchases/invoices/index.php">Purchase Invoices</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/crm/suppliers/index.php">Suppliers</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- HRM -->
                <?php if ($auth->hasModuleAccess('hrm') && $auth->hasPermission('hrm', 'view')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['PHP_SELF'], '/hr/') !== false ? 'active' : ''; ?>" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-tie me-1"></i> HR & Payroll
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/hr/employees/index.php">Employees</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/hr/attendance/index.php">Attendance</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/hr/leaves/index.php">Leaves</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/hr/payroll/index.php">Payroll</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/hr/payroll/components.php">Payroll Components</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/hr/settings.php">HR Settings</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Accounting -->
                <?php if ($auth->hasModuleAccess('accounting') && $auth->hasPermission('accounting', 'view')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['PHP_SELF'], '/accounting/') !== false ? 'active' : ''; ?>" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-calculator me-1"></i> Finance
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/accounting/accounts/index.php">Chart of Accounts</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/accounting/journal/index.php">Journal Entries</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/accounting/reports/balance-sheet.php">Financial Reports</a></li>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/accounting/reports/gst-reports.php">GST Reports</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Support -->
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/support/') !== false ? 'active' : ''; ?>" href="<?php echo MODULES_URL; ?>/support/index.php">
                        <i class="fas fa-headset me-1"></i> Support
                    </a>
                </li>
                
                 <!-- System -->
                 <?php if ($auth->hasPermission('settings', 'view')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo strpos($_SERVER['PHP_SELF'], '/settings/') !== false ? 'active' : ''; ?>" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs me-1"></i> System
                    </a>
                    <ul class="dropdown-menu">
                        <?php if ($auth->hasPermission('settings', 'edit')): ?>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/settings/company.php">Company Settings</a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/settings/index.php">System Settings</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Right Actions -->
            <div class="d-flex align-items-center gap-3">
                <a href="<?php echo MODULES_URL; ?>/sales/invoices/create.php" class="btn btn-primary btn-sm d-none d-lg-flex align-items-center gap-2">
                    <i class="fas fa-plus"></i> Invoice
                </a>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <?php if (!empty($user['avatar_path'])): ?>
                            <img src="<?php echo BASE_URL . $user['avatar_path']; ?>" alt="Avatar" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover; border: 2px solid rgba(255,255,255,0.2);">
                        <?php else: ?>
                            <div class="badge bg-primary rounded-circle p-2 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <span class="d-none d-lg-inline"><?php echo htmlspecialchars($user['full_name']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-header"><?php echo htmlspecialchars($user['roles'][0] ?? 'User'); ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        
                        <?php if ($auth->hasRole('Super Admin')): ?>
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/admin/dashboard.php">Admin Panel</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <li><a class="dropdown-item" href="<?php echo MODULES_URL; ?>/settings/profile.php">My Profile</a></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo MODULES_URL; ?>/auth/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
    /* Navbar Customization */
    .navbar-nav .nav-link {
        font-size: 0.9rem;
        font-weight: 500;
        color: rgba(255,255,255,0.75);
    }
    .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active {
        color: #fff;
    }
    .dropdown-menu {
        font-size: 0.9rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border: none;
    }
    .dropdown-item {
        padding: 8px 16px;
    }
    .dropdown-item:active {
        background-color: var(--primary-color);
    }
</style>

<?php
// FOOTER DATA FETCHING
// --------------------
$footerData = [];

// 1. Financial Year
$curMonth = date('n');
$curYear = date('Y');
if ($curMonth >= 4) {
    if ($curYear + 1 == 2000) { $yPart = '00'; } else { $yPart = substr($curYear + 1, -2); }
    $footerData['fy'] = $curYear . '-' . $yPart;
} else {
    $yPart = substr($curYear, -2);
    $footerData['fy'] = ($curYear - 1) . '-' . $yPart;
}

// 2. Company Details + GST + State
if (!isset($brandingSettings['gstin']) || !isset($brandingSettings['state'])) {
     // Ensure user is set
     if (!isset($user)) { $user = $auth->getCurrentUser(); }
     if (isset($user['company_id'])) {
         $companyDetails = $db->fetchOne("SELECT company_name, gstin, state, address_line1, city FROM company_settings WHERE id = ?", [$user['company_id']]);
         $footerData['company_name'] = $companyDetails['company_name'] ?? $appName;
         $footerData['gst'] = $companyDetails['gstin'] ?? 'N/A';
         $footerData['state'] = $companyDetails['state'] ?? 'N/A';
         $footerData['address'] = $companyDetails['city'] ?? '';
     }
} else {
     $footerData['company_name'] = $brandingSettings['app_name'];
     $footerData['gst'] = $brandingSettings['gstin'] ?? 'N/A';
     $footerData['state'] = $brandingSettings['state'] ?? 'N/A';
}

// 3. Subscription Validity
if (isset($user['company_id'])) {
    $subData = $db->fetchOne("SELECT current_period_end, status FROM subscriptions WHERE company_id = ? AND status = 'active' ORDER BY current_period_end DESC LIMIT 1", [$user['company_id']]);
    $footerData['validity'] = $subData ? date('d-m-Y', strtotime($subData['current_period_end'])) : 'Trial/Expired';
    $footerData['company_id'] = 'COMP' . str_pad($user['company_id'], 4, '0', STR_PAD_LEFT);
} else {
    $footerData['validity'] = 'N/A';
    $footerData['company_id'] = 'N/A';
}
?>

<!-- STICKY FOOTER -->
<div class="sticky-footer">
    <!-- Box 1: Brand -->
    <div class="footer-box brand-box">
        <?php if ($logoPath): ?>
            <img src="<?php echo $logoPath; ?>" alt="Logo" class="footer-logo">
        <?php else: ?>
            <div class="footer-brand-text">AC</div>
        <?php endif; ?>
    </div>

    <!-- Box 2: Company Info -->
    <div class="footer-box footer-info-box">
        <div class="info-row main">
            <strong><?php echo htmlspecialchars($footerData['company_name'] ?? 'Acculynce'); ?></strong>
        </div>
        <div class="info-row sub">
            (<?php echo htmlspecialchars($footerData['company_id'] ?? ''); ?>) <?php if(isset($footerData['address'])) echo '- ' . htmlspecialchars($footerData['address']); ?>
        </div>
    </div>

    <!-- Box 3: FY & GST -->
    <div class="footer-box fy-box">
        <div class="info-row">
            <span class="label">F.Y. :</span> <span class="value"><?php echo $footerData['fy'] ?? ''; ?></span>
        </div>
        <div class="info-row">
            <span class="label">GSTIN :</span> <span class="value"><?php echo htmlspecialchars($footerData['gst'] ?? 'N/A'); ?></span>
        </div>
    </div>

    <!-- Box 4: User & State -->
    <div class="footer-box user-box">
        <div class="info-row">
            <span class="label">User :</span> <span class="value"><?php echo htmlspecialchars($user['full_name'] ?? 'Guest'); ?></span>
        </div>
        <div class="info-row">
            <span class="label">State :</span> <span class="value"><?php echo htmlspecialchars($footerData['state'] ?? 'N/A'); ?></span>
        </div>
    </div>

    <!-- Box 5: License -->
    <div class="footer-box license-box">
        <div class="info-row" style="justify-content: center;">
            <span class="label">Licence Valid Upto :</span> <span class="value warning" style="margin-left: 5px;"><?php echo $footerData['validity'] ?? ''; ?></span>
        </div>
    </div>

    <!-- Box 6: Date -->
    <div class="footer-box date-box" style="background-color: #f3f4f6; border-left: 1px solid #e5e7eb;">
        <div class="info-row date-row" style="justify-content: center;">
            <span class="value" style="font-weight: 700; color: #4b5563;"><?php echo date('l, d-m-Y'); ?></span>
        </div>
    </div>
</div>

<style>
    /* Footer Layout */
    .sticky-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 48px; 
        background-color: #f1f5f9; 
        display: flex;
        border-top: 1px solid #cbd5e1;
        z-index: 1050;
        font-family: 'Segoe UI', sans-serif;
        font-size: 11px;
        color: #334155;
        box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    /* Common Box Style */
    .footer-box {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 0 16px;
        border-right: 1px solid #e2e8f0;
        white-space: nowrap;
        background: white;
    }
    
    .footer-box:last-child {
        border-right: none;
        background-color: #f8fafc;
        min-width: 140px;
    }
    
    .license-box {
        margin-left: auto; /* Push this and subsequent elements to the right */
        justify-content: center;
        padding: 4px 16px;
    }

    /* Box 1: Brand */
    .brand-box {
        background-color: #0f172a;
        color: white;
        flex-direction: row;
        align-items: center;
        gap: 12px;
        min-width: 160px;
        border-right: none;
    }
    .footer-logo { max-height: 32px; }
    .footer-brand-text { font-weight: bold; font-size: 16px; border: 2px solid white; padding: 2px 6px; }
    .brand-details { display: flex; flex-direction: column; line-height: 1.1; }
    .software-name { font-weight: 800; font-size: 11px; color: #60a5fa; text-transform: uppercase; }
    .vendor-name { font-size: 10px; color: #94a3b8; }

    /* Box 2: Info (Flexible width) */
    .footer-info-box {
        flex: 2; 
        border-left: 4px solid #3b82f6;
    }
    .info-row.main { font-weight: 800; font-size: 13px; color: #1e293b; margin-bottom: 2px; }
    .info-row.sub { color: #64748b; font-size: 11px; }

    /* Box 3, 4: Specific Data */
    .fy-box, .user-box {
        background-color: #f8fafc;
        min-width: 180px;
        justify-content: space-evenly;
        padding: 4px 16px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }
    .label { color: #64748b; font-weight: 500; }
    .value { font-weight: 700; color: #334155; }
    .value.warning { color: #dc2626; }
    
    .license-box {
        justify-content: space-evenly;
        padding: 4px 16px;
    }
    
    .date-row .value {
        color: #0f172a;
        font-size: 12px;
    }

    /* Layout Adjustments for Content */
    body {
        padding-bottom: 50px !important; /* Space for footer */
    }
</style>

