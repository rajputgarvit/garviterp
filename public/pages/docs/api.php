<?php
require_once '../../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Reference - <?php echo APP_NAME; ?> Docs</title>
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
        code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; font-family: monospace; color: #ef4444; }
        pre { background: #0f172a; color: #f8fafc; padding: 20px; border-radius: 8px; overflow-x: auto; margin: 20px 0; }
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
                <div class="nav-header">Guides</div>
                <a href="billing.php" class="nav-link">Billing & Payments</a>
                <a href="user-management.php" class="nav-link">User Management</a>
                <a href="inventory.php" class="nav-link">Inventory Guide</a>
                <a href="invoicing.php" class="nav-link">Invoicing Tutorial</a>
            </div>
            <div class="nav-group">
                <div class="nav-header">Developers</div>
                <a href="api.php" class="nav-link active">API Reference</a>
            </div>
        </aside>

        <main class="docs-content">
            <div class="breadcrumb">
                <a href="../documentation.php">Docs</a>
                <i class="fas fa-chevron-right" style="font-size: 0.8rem;"></i>
                <span>API Reference</span>
            </div>

            <h1 class="doc-title">API Reference</h1>
            
            <div class="doc-body">
                <p>The Acculynce API is organized around REST. Our API has predictable resource-oriented URLs, accepts form-encoded request bodies, returns JSON-encoded responses, and uses standard HTTP response codes.</p>

                <h2>Authentication</h2>
                <p>Authenticate your API requests using your API keys. You can manage your API keys in the Dashboard.</p>
                <pre>Authorization: Bearer YOUR_API_KEY</pre>

                <h2>Endpoints</h2>
                <p>Here are some common endpoints you might use:</p>
                <ul>
                    <li><code>GET /api/v1/invoices</code> - List all invoices</li>
                    <li><code>POST /api/v1/invoices</code> - Create a new invoice</li>
                    <li><code>GET /api/v1/stock/{sku}</code> - Retrieve stock level for a product</li>
                </ul>

                <h2>Rate Limiting</h2>
                <p>We limit API requests to 100 requests per minute per IP address. If you exceed this, you will receive a <code>429 Too Many Requests</code> response.</p>
            </div>
        </main>
    </div>

    <?php require_once '../../../includes/public_footer.php'; ?>
</body>
</html>
