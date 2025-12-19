<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php 
    $pageTitle = "Product Roadmap";
    $pageDescription = "See what's coming next Acculynce. We are constantly innovating to provide the best storage and business management solutions.";
    require_once '../../includes/public_meta.php'; 
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/landing.css">
    <style>
        .page-header {
            padding: 160px 0 100px;
            text-align: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        .page-title {
            font-size: 3rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 24px;
            background: linear-gradient(135deg, var(--primary-color), #4f46e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .page-subtitle {
            font-size: 1.25rem;
            color: #64748b;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .roadmap-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            padding: 80px 0;
        }
        
        .roadmap-column {
            background: #f8fafc;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid #e2e8f0;
        }
        
        .column-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .column-badge {
            font-size: 0.8rem;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 50px;
            text-transform: uppercase;
        }
        
        .badge-now { background: #dcfce7; color: #15803d; }
        .badge-next { background: #dbeafe; color: #1d4ed8; }
        .badge-later { background: #f1f5f9; color: #64748b; }
        
        .column-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
        }
        
        .roadmap-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
            transition: transform 0.2s;
        }
        
        .roadmap-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        
        .card-tag {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 4px;
            margin-bottom: 12px;
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
        }
        
        .card-desc {
            font-size: 0.95rem;
            color: #64748b;
            line-height: 1.5;
        }

        .cta-box {
            background: white;
            border-radius: 16px;
            padding: 48px;
            text-align: center;
            border: 2px dashed #e2e8f0;
            margin-top: 40px;
        }

        @media (max-width: 900px) {
            .roadmap-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/public_header.php'; ?>

    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Product Roadmap</h1>
            <p class="page-subtitle">We're constantly improving. Here is a glimpse of what our team is working on to help your business grow.</p>
        </div>
    </header>

    <div class="container">
        <div class="roadmap-grid">
            <!-- Now Column -->
            <div class="roadmap-column">
                <div class="column-header">
                    <span class="column-badge badge-now">In Progress</span>
                    <span class="column-title">Q1 2026</span>
                </div>
                
                <div class="roadmap-card">
                    <span class="card-tag">Mobile</span>
                    <h3 class="card-title">Mobile App Beta</h3>
                    <p class="card-desc">Complete mobile experience for iOS and Android. Manage inventory and orders on the go.</p>
                </div>
                
                <div class="roadmap-card">
                    <span class="card-tag">AI & Data</span>
                    <h3 class="card-title">AI Forecasting</h3>
                    <p class="card-desc">Predictive inventory analytics to prevent stockouts and overstocking using historical data.</p>
                </div>

                <div class="roadmap-card">
                    <span class="card-tag">Core</span>
                    <h3 class="card-title">Advanced Permissions</h3>
                    <p class="card-desc">Granular role-based access control for enterprise teams with custom role creation.</p>
                </div>
            </div>

            <!-- Next Column -->
            <div class="roadmap-column">
                <div class="column-header">
                    <span class="column-badge badge-next">Up Next</span>
                    <span class="column-title">Q2 2026</span>
                </div>
                
                <div class="roadmap-card">
                    <span class="card-tag">Finance</span>
                    <h3 class="card-title">Multi-Currency</h3>
                    <p class="card-desc">Native support for transactions in over 150 currencies with real-time exchange rates.</p>
                </div>
                
                <div class="roadmap-card">
                    <span class="card-tag">Integrations</span>
                    <h3 class="card-title">Shopify Sync</h3>
                    <p class="card-desc">Two-way synchronization for products, orders, and inventory with Shopify stores.</p>
                </div>
                
                <div class="roadmap-card">
                    <span class="card-tag">HR</span>
                    <h3 class="card-title">Employee Portal</h3>
                    <p class="card-desc">Self-service portal for employees to view payslips, request leave, and update profiles.</p>
                </div>
            </div>

            <!-- Later Column -->
            <div class="roadmap-column">
                <div class="column-header">
                    <span class="column-badge badge-later">Planned</span>
                    <span class="column-title">Future</span>
                </div>
                
                <div class="roadmap-card">
                    <span class="card-tag">Platform</span>
                    <h3 class="card-title">API Marketplace</h3>
                    <p class="card-desc">Public API and marketplace for third-party developers to build add-ons.</p>
                </div>
                
                <div class="roadmap-card">
                    <span class="card-tag">Communication</span>
                    <h3 class="card-title">Slack Integration</h3>
                    <p class="card-desc">Get notifications for critical business events directly in your team's Slack channels.</p>
                </div>
                 
                <div class="roadmap-card">
                    <span class="card-tag">Reporting</span>
                    <h3 class="card-title">Custom Report Builder</h3>
                    <p class="card-desc">Drag-and-drop interface to create custom analytics reports and dashboards.</p>
                </div>
            </div>
        </div>

        <div class="cta-box">
            <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 12px; color: #1e293b;">Have a feature request?</h3>
            <p style="color: #64748b; margin-bottom: 24px;">We build what you need. Let us know what would make your life easier.</p>
            <a href="../contact.php" class="btn btn-primary" style="padding: 12px 24px; border-radius: 8px;">Suggest a Feature</a>
        </div>
        
        <div style="height: 100px;"></div>
    </div>

    <?php require_once '../../includes/public_footer.php'; ?>
</body>
</html>
