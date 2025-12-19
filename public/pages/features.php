<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php 
    $pageTitle = "Features";
    $pageDescription = "Explore the powerful features of Acculynce. From Inventory and Finance to HR and Sales, everything you need to run your business.";
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
        
        .feature-category {
            padding: 80px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .category-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .category-badge {
            display: inline-block;
            padding: 6px 16px;
            background: #e0e7ff;
            color: #4338ca;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .category-title {
            font-size: 2.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
        }
        
        .category-desc {
            font-size: 1.1rem;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
        }

        .feature-card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: var(--primary-color);
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            background: #f1f5f9;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 24px;
            transition: all 0.3s;
        }
        
        .feature-card:hover .feature-icon {
            background: var(--primary-color);
            color: white;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .feature-text {
            color: #64748b;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        @media (max-width: 1024px) {
            .features-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .page-title { font-size: 2.5rem; }
            .features-grid { grid-template-columns: 1fr; }
            .page-header { padding: 120px 0 60px; }
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/public_header.php'; ?>

    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Powerful Features</h1>
            <p class="page-subtitle">Discover all the tools you need to run your business efficiently from a single platform. Built for scale, designed for simplicity.</p>
        </div>
    </header>

    <!-- Financial Management -->
    <section class="feature-category">
        <div class="container">
            <div class="category-header">
                <span class="category-badge">Finance</span>
                <h2 class="category-title">Financial Management</h2>
                <p class="category-desc">Take control of your cash flow with enterprise-grade accounting tools simplified for modern businesses.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    <h3 class="feature-title">Smart Invoicing</h3>
                    <p class="feature-text">Create professional, GST-compliant invoices in seconds. Automate recurring invoices and payment reminders.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-receipt"></i></div>
                    <h3 class="feature-title">Expense Tracking</h3>
                    <p class="feature-text">Snap photos of receipts and let AI categorize expenses. Keep track of every penny leaving your business.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h3 class="feature-title">Real-time Reporting</h3>
                    <p class="feature-text">Generate P&L statements, balance sheets, and cash flow reports instantly. Make data-driven decisions.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-university"></i></div>
                    <h3 class="feature-title">Bank Reconciliation</h3>
                    <p class="feature-text">Connect your bank accounts and automatically reconcile transactions to save hours of manual data entry.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-calculator"></i></div>
                    <h3 class="feature-title">Tax Management</h3>
                    <p class="feature-text">Automate GST and tax calculations tailored to your region. Stay compliant without the headache.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-file-contract"></i></div>
                    <h3 class="feature-title">Estimates & Quotes</h3>
                    <p class="feature-text">Send professional estimates to clients and convert them to invoices with a single click upon approval.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Inventory Management -->
    <section class="feature-category" style="background: #f8fafc;">
        <div class="container">
            <div class="category-header">
                <span class="category-badge">Operations</span>
                <h2 class="category-title">Inventory & Supply Chain</h2>
                <p class="category-desc">Optimize your stock levels, manage multiple warehouses, and never miss a sale due to outages.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-boxes"></i></div>
                    <h3 class="feature-title">Multi-Warehouse</h3>
                    <p class="feature-text">Manage inventory across multiple locations. Transfer stock easily and view consolidated levels.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-barcode"></i></div>
                    <h3 class="feature-title">Barcode Scanning</h3>
                    <p class="feature-text">Speed up operations with integrated barcode support for receiving, picking, and packing.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-bell"></i></div>
                    <h3 class="feature-title">Low Stock Alerts</h3>
                    <p class="feature-text">Get notified automatically when items hit reorder points. Generate purchase orders instantly.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- HR & People -->
    <section class="feature-category">
        <div class="container">
            <div class="category-header">
                <span class="category-badge">Human Resources</span>
                <h2 class="category-title">HR & Payroll</h2>
                <p class="category-desc">Build a better workplace. Manage your team from onboarding to payroll in one place.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <h3 class="feature-title">Employee Database</h3>
                    <p class="feature-text">Centralize employee records, documents, IDs, and contract details securely.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-money-check-alt"></i></div>
                    <h3 class="feature-title">Automated Payroll</h3>
                    <p class="feature-text">Process payroll in minutes. Handle deductions, bonuses, and tax computations automatically.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                    <h3 class="feature-title">Attendance & Leave</h3>
                    <p class="feature-text">Track attendance with biometric integration or web check-in. Manage leave requests easily.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" style="padding: 100px 0; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; text-align: center;">
        <div class="container">
            <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 20px;">Ready to transform your business?</h2>
            <p style="font-size: 1.25rem; color: #94a3b8; margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto;">Join thousands of companies using Acculynce to streamline their operations.</p>
            <a href="../../modules/auth/register.php" class="btn btn-primary" style="padding: 18px 40px; font-size: 1.1rem; border-radius: 50px;">Start Your Free Trial</a>
        </div>
    </section>

    <?php require_once '../../includes/public_footer.php'; ?>
</body>
</html>
