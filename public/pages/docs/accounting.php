<?php
require_once '../../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Guide - <?php echo APP_NAME; ?> Docs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/landing.css">
    <style>
        .docs-container { display: flex; min-height: 100vh; padding-top: 80px; }
        .docs-sidebar { width: 300px; background: #f8fafc; border-right: 1px solid #e2e8f0; padding: 40px 24px; position: sticky; top: 80px; height: calc(100vh - 80px); overflow-y: auto; flex-shrink: 0; }
        .docs-content { flex: 1; padding: 60px 80px; }
        .breadcrumb { display: flex; align-items: center; gap: 8px; color: #64748b; margin-bottom: 32px; font-size: 0.95rem; }
        .breadcrumb a { color: #64748b; text-decoration: none; }
        .breadcrumb a:hover { color: var(--primary-color); }
        .doc-title { font-size: 2.5rem; font-weight: 800; margin-bottom: 24px; color: #0f172a; }
        .doc-body { font-size: 1.1rem; line-height: 1.8; color: #334155; max-width: 800px; }
        .doc-body h2 { font-size: 1.75rem; font-weight: 700; margin-top: 48px; margin-bottom: 24px; color: #1e293b; }
        .nav-group { margin-bottom: 32px; }
        .nav-header { font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin-bottom: 16px; letter-spacing: 0.05em; }
        .nav-link { display: block; padding: 8px 12px; color: #475569; text-decoration: none; border-radius: 6px; margin-bottom: 4px; font-weight: 500; }
        .nav-link:hover { background: #e2e8f0; color: #1e293b; }
        .nav-link.active { background: #eff6ff; color: var(--primary-color); }
        @media (max-width: 900px) { .docs-sidebar { display: none; } .docs-content { margin-left: 0; padding: 40px 24px; } }
    </style>
</head>
<body>
    <?php require_once '../../../includes/public_header.php'; ?>

    <div class="docs-container">
        <aside class="docs-sidebar">
            <div class="nav-group">
                <div class="nav-header">Getting Started</div>
                <a href="getting-started.php" class="nav-link">Introduction</a>
            </div>
            <div class="nav-group">
                <div class="nav-header">Modules</div>
                <a href="accounting.php" class="nav-link active">Accounting</a>
                <a href="crm.php" class="nav-link">CRM</a>
                <a href="hr.php" class="nav-link">HR & Payroll</a>
                <a href="inventory.php" class="nav-link">Inventory</a>
                <a href="purchases.php" class="nav-link">Purchases</a>
                <a href="reports.php" class="nav-link">Reports</a>
                <a href="invoicing.php" class="nav-link">Sales & Invoicing</a>
                <a href="support.php" class="nav-link">Support</a>
            </div>
            <div class="nav-group">
                <div class="nav-header">Administration</div>
                <a href="billing.php" class="nav-link">Billing & Utils</a>
                <a href="user-management.php" class="nav-link">Users & Roles</a>
            </div>
            <div class="nav-group">
                <div class="nav-header">Developers</div>
                <a href="api.php" class="nav-link">API Reference</a>
            </div>
        </aside>

        <main class="docs-content">
            <div class="breadcrumb">
                <a href="../documentation.php">Docs</a>
                <i class="fas fa-chevron-right" style="font-size: 0.8rem;"></i>
                <span>Accounting</span>
            </div>

            <h1 class="doc-title">Accounting & Finance</h1>
            
            <div class="doc-body">
                <p>Manage your company's financials with the robust Accounting module, including Chart of Accounts, Journal Entries, and Balance Sheets.</p>

                <h2>Chart of Accounts</h2>
                <p>The foundation of your accounting. We provide a default set of accounts (Assets, Liabilities, Equity, Income, Expenses), but you can customize them to fit your business structure.</p>

                <h2>Journal Entries</h2>
                <p>Record financial transactions manually for adjustments, opening balances, or complex transfers. Each entry supports multiple debits and credits and must balance before saving.</p>

                <h2>Fiscal Years</h2>
                <p>Define your financial years to segregate data for reporting. You can close fiscal years to prevent further edits to past transactions.</p>
                
                <h2>General Ledger</h2>
                <p>View the complete history of transactions for any account. This is essential for audits and tracking the flow of money through your business.</p>
            </div>
        </main>
    </div>

    <?php require_once '../../../includes/public_footer.php'; ?>
</body>
</html>
