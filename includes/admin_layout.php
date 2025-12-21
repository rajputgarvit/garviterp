<?php
if (session_status() === PHP_SESSION_NONE) {
    // session_start(); // Handled in config.php
}

// Ensure user is logged in and is an admin
require_once __DIR__ . '/../classes/Auth.php';
$auth = new Auth();
$auth->requireLogin();

if (!$auth->hasPermission('super_admin', 'view')) {
    header('Location: ' . MODULES_URL . '/dashboard/index.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$pageTitle = isset($pageTitle) ? $pageTitle : 'Admin Panel';

// Fetch branding settings
$db = Database::getInstance();
$brandingSettings = $db->fetchOne("SELECT app_name, logo_path, theme_color, gstin, state FROM company_settings WHERE id = ? LIMIT 1", [$currentUser['company_id'] ?? 0]);

// Fetch Notification Counts
$inqCount = $db->fetchOne("SELECT COUNT(*) as count FROM contact_requests WHERE status = 'New'");
$tktCount = $db->fetchOne("SELECT COUNT(*) as count FROM support_tickets WHERE status = 'Open'");
$inquiryCount = $inqCount['count'] ?? 0;
$ticketCount = $tktCount['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Admin Specific Overrides */
        :root {
            --admin-navbar-bg: #1e1e2d;
            --admin-navbar-text: #a2a3b7;
            --admin-navbar-hover: #1b1b28;
            --admin-accent: #3699ff;
        }
        
        body {
            background-color: #f3f4f6;
        }

        /* Top Navbar Styling */
        .admin-navbar {
            background-color: var(--admin-navbar-bg);
            padding: 0.25rem 1rem; /* Reduced padding */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            min-height: 48px;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .navbar-brand img {
            max-height: 24px; /* Reduced logo size */
        }

        .nav-link {
            color: var(--admin-navbar-text) !important;
            font-weight: 500;
            padding: 0.4rem 0.8rem !important; /* Compact links */
            transition: all 0.2s;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .nav-link:hover, .nav-link.active {
            color: white !important;
            background-color: rgba(255,255,255,0.05);
        }

        .nav-link.active {
            color: var(--admin-accent) !important;
        }

        /* Dropdown Styling */
        .dropdown-menu {
            background-color: #1e1e2d;
            border: 1px solid rgba(255,255,255,0.1);
            margin-top: 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            padding: 0.5rem 0;
        }
        
        .dropdown-item {
            color: var(--admin-navbar-text);
            padding: 6px 16px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
            color: var(--admin-navbar-text);
        }

        .dropdown-item:hover {
            background-color: var(--admin-navbar-hover);
            color: white;
        }
        
        .dropdown-item:hover i {
            color: var(--admin-accent);
        }

        .dropdown-item.text-danger:hover {
            background-color: rgba(220, 38, 38, 0.1);
            color: #ef4444;
        }
        .dropdown-item.text-danger i {
            color: #ef4444;
        }

        /* Badge Styling */
        .badge-notification {
            font-size: 0.65rem;
            padding: 0.2em 0.5em;
        }

        /* Content Area Adjustment */
        .dashboard-wrapper {
            display: block; /* Override default flex */
            min-height: 100vh;
            padding-top: 0 !important; /* Force remove top padding inherited from style.css */
        }
        
        .main-content {
            margin-left: 0 !important; /* Reset sidebar margin */
            width: 100%;
        }

        .top-header-admin {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .user-menu-admin {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.8);
        }
        
        .user-avatar-sm {
            width: 32px;
            height: 32px;
            background: var(--admin-accent);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .navbar-collapse {
                background: var(--admin-navbar-bg);
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                z-index: 1000;
                padding: 1rem;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Top Navigation Bar -->
        <nav class="navbar navbar-expand-lg admin-navbar">
            <div class="container-fluid">
                <!-- Brand -->
                <a class="navbar-brand" href="<?php echo MODULES_URL; ?>/admin/dashboard.php">
                    <?php if (!empty($brandingSettings['logo_path'])): ?>
                        <img src="<?php echo BASE_URL . $brandingSettings['logo_path']; ?>" alt="Logo">
                    <?php else: ?>
                        <i class="fas fa-shield-alt text-primary"></i>
                    <?php endif; ?>
                    <span>Acculynce Systems Admin</span>
                </a>

                <!-- Toggler -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"><i class="fas fa-bars text-white"></i></span>
                </button>

                <!-- Menu Items -->
                <div class="collapse navbar-collapse" id="adminNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>" href="<?php echo MODULES_URL; ?>/admin/dashboard.php">
                                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                            </a>
                        </li>

                        <!-- Platform Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['users', 'companies', 'subscriptions', 'reports', 'tickets', 'inquiries']) ? 'active' : ''; ?>" href="#" id="platformDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Platform
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="platformDropdown">
                                <li>
                                    <a class="dropdown-item" href="<?php echo MODULES_URL; ?>/admin/users.php">
                                        <div class="d-flex align-items-center"><i class="fas fa-users"></i> Users</div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo MODULES_URL; ?>/admin/companies.php">
                                        <div class="d-flex align-items-center"><i class="fas fa-building"></i> Companies</div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo MODULES_URL; ?>/admin/subscriptions.php">
                                        <div class="d-flex align-items-center"><i class="fas fa-credit-card"></i> Subscriptions</div>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider bg-secondary opacity-25"></li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo MODULES_URL; ?>/admin/reports.php">
                                        <div class="d-flex align-items-center"><i class="fas fa-chart-bar"></i> Reports</div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo MODULES_URL; ?>/admin/tickets.php">
                                        <div class="d-flex align-items-center"><i class="fas fa-headset"></i> Tickets</div>
                                        <?php if ($ticketCount > 0): ?>
                                            <span class="badge bg-danger badge-notification rounded-pill"><?php echo $ticketCount; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo MODULES_URL; ?>/admin/inquiries.php">
                                        <div class="d-flex align-items-center"><i class="fas fa-envelope-open-text"></i> Inquiries</div>
                                        <?php if ($inquiryCount > 0): ?>
                                            <span class="badge bg-danger badge-notification rounded-pill"><?php echo $inquiryCount; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- System Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['roles', 'audit_logs', 'export_data', 'broadcasts', 'settings', 'reset_database']) ? 'active' : ''; ?>" href="#" id="systemDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                System
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="systemDropdown">
                                <li>
                                    <a class="dropdown-item" href="<?php echo MODULES_URL; ?>/settings/roles.php">
                                        <div><i class="fas fa-user-tag"></i> Roles & Permissions</div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo MODULES_URL; ?>/settings/audit_logs.php">
                                        <div><i class="fas fa-history"></i> Audit Logs</div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo MODULES_URL; ?>/settings/export_data.php">
                                        <div><i class="fas fa-file-export"></i> Data Export</div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo MODULES_URL; ?>/admin/broadcasts.php">
                                        <div><i class="fas fa-bullhorn"></i> Broadcasts</div>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider bg-secondary opacity-25"></li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo MODULES_URL; ?>/admin/settings.php">
                                        <div><i class="fas fa-cogs"></i> System Settings</div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo MODULES_URL; ?>/admin/reset_database.php">
                                        <div class="text-danger"><i class="fas fa-database text-danger"></i> Reset Database</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo MODULES_URL; ?>/dashboard/index.php" target="_blank">
                                <i class="fas fa-external-link-alt me-1"></i> Go to ERP
                            </a>
                        </li>
                    </ul>

                    <!-- User Menu -->
                    <div class="user-menu-admin ms-auto">
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar-sm">
                                    <?php echo strtoupper(substr($currentUser['full_name'], 0, 1)); ?>
                                </div>
                                <span class="d-none d-lg-block text-white small"><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle"></i> Profile</a></li>
                                <li><hr class="dropdown-divider bg-secondary opacity-25"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo MODULES_URL; ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Breadcrumbs / Page Header Area (Optional, keeping it clean for now) -->
            <div class="top-header-admin">
                <div class="header-left">
                    <h1 class="h4 mb-0"><?php echo $pageTitle; ?></h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="<?php echo MODULES_URL; ?>/admin/dashboard.php">Admin</a></li>
                            <li class="breadcrumb-item active"><?php echo $pageTitle; ?></li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="content-area">
                <!-- Content injected here -->
                
    <!-- Sticky Footer for Admin -->
    <?php
    // Footer Data Logic
    $footerData = [];
    $curMonth = date('n');
    $curYear = date('Y');
    if ($curMonth >= 4) {
        if ($curYear + 1 == 2000) { $yPart = '00'; } else { $yPart = substr($curYear + 1, -2); }
        $footerData['fy'] = $curYear . '-' . $yPart;
    } else {
        $yPart = substr($curYear, -2);
        $footerData['fy'] = ($curYear - 1) . '-' . $yPart;
    }

    if (!empty($brandingSettings)) {
        $footerData['company_name'] = $brandingSettings['app_name'] ?? 'Acculynce Systems';
        $footerData['gst'] = $brandingSettings['gstin'] ?? 'Not Set';
        $footerData['state'] = $brandingSettings['state'] ?? 'Delhi'; 
    } else {
        $footerData['company_name'] = 'Acculynce Systems';
        $footerData['gst'] = 'N/A';
        $footerData['state'] = 'N/A';
    }
    
    // Admin usually manages the platform, so "License" is N/A or "Unlimited"
    $footerData['validity'] = 'Unlimited (Super Admin)';
    $footerData['company_id'] = 'ADMIN';
    ?>
    <style>
        /* Footer Layout Admin Override */
        .sticky-footer-admin {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 48px; 
            background-color: #1e1e2d; /* Dark for Admin */
            display: flex;
            border-top: 1px solid #2b2b40;
            z-index: 1050;
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            color: #a2a3b7;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.2);
        }

        .footer-box-admin {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 16px;
            border-right: 1px solid #2b2b40;
            white-space: nowrap;
        }

        .footer-box-admin:last-child {
            border-right: none;
            margin-left: auto;
            background-color: #1b1b28;
            min-width: 220px;
        }

        .brand-box-admin {
            background-color: #1b1b28;
            color: white;
            flex-direction: row;
            align-items: center;
            gap: 12px;
            min-width: 160px;
        }
        
        .footer-logo-admin { max-height: 28px; }
        .footer-brand-text-admin { border: 1px solid #a2a3b7; padding: 2px 4px; color: white; }

        .info-box-admin {
            flex: 2;
            border-left: 4px solid #3699ff;
        }
        .info-row.main { font-weight: 700; font-size: 13px; color: #ffffff; margin-bottom: 2px; }
        .info-row.sub { color: #7e8299; font-size: 11px; }

        .fy-box-admin, .user-box-admin {
            background-color: #1e1e2d;
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
        .label { color: #5e6278; font-weight: 500; }
        .value { font-weight: 600; color: #cdcdde; }
        
        .date-row .value { color: #ffffff; }

        body { padding-bottom: 50px !important; }
    </style>

    <div class="sticky-footer-admin">
        <!-- Box 1: Brand -->
        <div class="footer-box-admin brand-box-admin">
            <?php if (!empty($brandingSettings['logo_path'])): ?>
                <img src="<?php echo BASE_URL . $brandingSettings['logo_path']; ?>" alt="Logo" class="footer-logo-admin">
            <?php else: ?>
                <div class="footer-brand-text-admin">AC</div>
            <?php endif; ?>
            <div class="brand-details">
                <span style="font-weight: 800; font-size: 11px; color: #3699ff; text-transform: uppercase;">Acculynce ERP</span>
            </div>
        </div>

        <!-- Box 2: Info -->
        <div class="footer-box-admin info-box-admin">
            <div class="info-row main">
                <?php echo htmlspecialchars($footerData['company_name']); ?>
            </div>
            <div class="info-row sub">
                (ADMIN PANEL) - Super Admin Console
            </div>
        </div>

        <!-- Box 3: FY -->
        <div class="footer-box-admin fy-box-admin">
            <div class="info-row">
                <span class="label">F.Y. :</span> <span class="value"><?php echo $footerData['fy']; ?></span>
            </div>
            <div class="info-row">
                <span class="label">GSTIN :</span> <span class="value"><?php echo htmlspecialchars($footerData['gst']); ?></span>
            </div>
        </div>
        
        <!-- Box 4: User -->
        <div class="footer-box-admin user-box-admin">
            <div class="info-row">
                <span class="label">User :</span> <span class="value"><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Role :</span> <span class="value">Super Admin</span>
            </div>
        </div>

        <!-- Box 5: Date -->
        <div class="footer-box-admin">
           <div class="info-row">
                <span class="label">System Status :</span> <span class="value" style="color: #50cd89;">Active</span>
            </div>
            <div class="info-row date-row">
                <span class="value"><?php echo date('l, d-m-Y'); ?></span>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

