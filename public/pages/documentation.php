<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php 
    $pageTitle = "Documentation";
    $pageDescription = "Comprehensive guides and documentation for Acculynce. Learn how to manage your business operations effectively.";
    require_once '../../includes/public_meta.php'; 
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/landing.css">
    <style>
        .page-header {
            padding: 160px 0 80px;
            text-align: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
        }
        .page-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 24px;
            color: white;
        }
        .page-subtitle {
            font-size: 1.25rem;
            color: #94a3b8;
            max-width: 700px;
            margin: 0 auto 40px;
            line-height: 1.6;
        }
        
        .search-container {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 16px 24px 16px 50px;
            border-radius: 50px;
            border: none;
            font-size: 1.1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .docs-section {
            padding: 80px 0;
            background: #f8fafc;
        }

        .docs-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
        }

        .doc-card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
            text-decoration: none;
            display: block;
        }

        .doc-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }

        .doc-icon {
            font-size: 1.75rem;
            color: var(--primary-color);
            margin-bottom: 16px;
            background: #eff6ff;
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .doc-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .doc-desc {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .popular-articles {
            padding: 80px 0;
            background: white;
        }

        .article-list {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            max-width: 800px;
            margin: 0 auto;
        }

        .article-item a {
            display: flex;
            align-items: center;
            padding: 16px;
            border-radius: 8px;
            background: #f8fafc;
            color: #334155;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }

        .article-item a:hover {
            background: #f1f5f9;
            color: var(--primary-color);
        }

        .article-item i {
            margin-right: 12px;
            color: #94a3b8;
        }

        @media (max-width: 768px) {
            .docs-grid { grid-template-columns: 1fr; }
            .article-list { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/public_header.php'; ?>

    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Documentation</h1>
            <p class="page-subtitle">Everything you need to know to get started, manage your account, and integrate with our API.</p>
            
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search for guides, API references, topics...">
            </div>
        </div>
    </header>

    <section class="docs-section">
        <div class="container">
            <div class="docs-grid">
                <a href="docs/getting-started.php" class="doc-card">
                    <div class="doc-icon"><i class="fas fa-flag"></i></div>
                    <h3 class="doc-title">Getting Started</h3>
                    <p class="doc-desc">Account setup, basic configuration, and your first steps with Acculynce.</p>
                </a>
                
                <a href="docs/api.php" class="doc-card">
                    <div class="doc-icon"><i class="fas fa-code"></i></div>
                    <h3 class="doc-title">API Reference</h3>
                    <p class="doc-desc">Comprehensive API documentation for developers and integrators.</p>
                </a>

                <a href="docs/billing.php" class="doc-card">
                    <div class="doc-icon"><i class="fas fa-credit-card"></i></div>
                    <h3 class="doc-title">Billing & Payments</h3>
                    <p class="doc-desc">Manage your subscription, view invoices, and update payment methods.</p>
                </a>

                <a href="docs/user-management.php" class="doc-card">
                    <div class="doc-icon"><i class="fas fa-users-cog"></i></div>
                    <h3 class="doc-title">User Management</h3>
                    <p class="doc-desc">Learn how to add team members, assign roles, and manage permissions.</p>
                </a>

                <a href="docs/inventory.php" class="doc-card">
                    <div class="doc-icon"><i class="fas fa-cubes"></i></div>
                    <h3 class="doc-title">Inventory Guide</h3>
                    <p class="doc-desc">Deep dive into stock management, warehouses, and adjustments.</p>
                </a>

                <a href="docs/invoicing.php" class="doc-card">
                    <div class="doc-icon"><i class="fas fa-file-invoice"></i></div>
                    <h3 class="doc-title">Invoicing Tutorial</h3>
                    <p class="doc-desc">Creating custom templates, automated reminders, and tax settings.</p>
                </a>
            </div>
        </div>
    </section>

    <section class="popular-articles">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 40px; font-weight: 800;">Popular Articles</h2>
            <ul class="article-list">
                <li class="article-item"><a href="#"><i class="fas fa-file-alt"></i> How to reset your password</a></li>
                <li class="article-item"><a href="#"><i class="fas fa-file-alt"></i> Setting up 2FA for your account</a></li>
                <li class="article-item"><a href="#"><i class="fas fa-file-alt"></i> Importing data from Excel</a></li>
                <li class="article-item"><a href="#"><i class="fas fa-file-alt"></i> Integrating with Stripe</a></li>
                <li class="article-item"><a href="#"><i class="fas fa-file-alt"></i> Customizing your dashboard</a></li>
                <li class="article-item"><a href="#"><i class="fas fa-file-alt"></i> Generating tax reports</a></li>
            </ul>
        </div>
    </section>

    <?php require_once '../../includes/public_footer.php'; ?>
</body>
</html>
