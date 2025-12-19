<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php 
    $pageTitle = "Security";
    $pageDescription = "Enterprise-grade security for your business data. Learn how Acculynce protects your organization's sensitive information.";
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

        .security-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            padding: 80px 0;
        }
        
        .security-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            display: flex;
            gap: 24px;
            transition: all 0.3s;
        }

        .security-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .icon-box {
            width: 64px;
            height: 64px;
            background: #f0f9ff;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .card-content h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #0f172a;
        }

        .card-content p {
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 16px;
        }

        .card-list {
            list-style: none;
            padding: 0;
        }

        .card-list li {
            font-size: 0.95rem;
            color: #475569;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-list li i {
            color: #10b981;
            font-size: 0.8rem;
        }

        .compliance-section {
            background: #f8fafc;
            padding: 100px 0;
            text-align: center;
        }

        .compliance-grid {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 48px;
            margin-top: 60px;
            opacity: 0.7;
        }

        .compliance-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            color: #475569;
        }

        @media (max-width: 900px) {
            .security-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/public_header.php'; ?>

    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Uncompromising Security</h1>
            <p class="page-subtitle">We protect your data with bank-grade encryption, rigorous compliance standards, and 24/7 threat monitoring.</p>
        </div>
    </header>

    <div class="container">
        <div class="security-grid">
            <div class="security-card">
                <div class="icon-box"><i class="fas fa-lock"></i></div>
                <div class="card-content">
                    <h3>Data Encryption</h3>
                    <p>Your data is encrypted at rest and in transit using industry-standard protocols.</p>
                    <ul class="card-list">
                        <li><i class="fas fa-check"></i> AES-256 encryption for stored data</li>
                        <li><i class="fas fa-check"></i> TLS 1.3 for data in transit</li>
                        <li><i class="fas fa-check"></i> Secure key management</li>
                    </ul>
                </div>
            </div>

            <div class="security-card">
                <div class="icon-box"><i class="fas fa-server"></i></div>
                <div class="card-content">
                    <h3>Infrastructure Security</h3>
                    <p>Hosted on secure, compliant cloud infrastructure with redundancy and failover.</p>
                    <ul class="card-list">
                        <li><i class="fas fa-check"></i> DDoS protection</li>
                        <li><i class="fas fa-check"></i> Automated backups</li>
                        <li><i class="fas fa-check"></i> 99.9% Uptime SLA</li>
                    </ul>
                </div>
            </div>

            <div class="security-card">
                <div class="icon-box"><i class="fas fa-user-shield"></i></div>
                <div class="card-content">
                    <h3>Access Control</h3>
                    <p>Granular permissions ensure employees only access what they need.</p>
                    <ul class="card-list">
                        <li><i class="fas fa-check"></i> Role-Based Access Control (RBAC)</li>
                        <li><i class="fas fa-check"></i> Multi-Factor Authentication (MFA)</li>
                        <li><i class="fas fa-check"></i> Session management & timeouts</li>
                    </ul>
                </div>
            </div>

            <div class="security-card">
                <div class="icon-box"><i class="fas fa-bug"></i></div>
                <div class="card-content">
                    <h3>Vulnerability Management</h3>
                    <p>We proactively hunt for threats and regularly test our systems.</p>
                    <ul class="card-list">
                        <li><i class="fas fa-check"></i> Regular penetration testing</li>
                        <li><i class="fas fa-check"></i> Automated code scanning</li>
                        <li><i class="fas fa-check"></i> Bug bounty program</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <section class="compliance-section">
        <div class="container">
            <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 20px; color: #0f172a;">Built for Compliance</h2>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">We adhere to global standards to ensure your data is handled responsibly and legally.</p>
            
            <div class="compliance-grid">
                <div class="compliance-item"><i class="fas fa-shield-alt"></i> GDPR Ready</div>
                <div class="compliance-item"><i class="fas fa-file-contract"></i> SOC 2 Type II</div>
                <div class="compliance-item"><i class="fas fa-lock"></i> ISO 27001</div>
                <div class="compliance-item"><i class="fas fa-id-card"></i> HIPAA</div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" style="padding: 100px 0; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; text-align: center;">
        <div class="container">
            <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 20px;">Trust us with your business</h2>
            <p style="font-size: 1.25rem; color: #94a3b8; margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto;">Join thousands of security-conscious companies running on Acculynce.</p>
            <a href="../../modules/auth/register.php" class="btn btn-primary" style="padding: 18px 40px; font-size: 1.1rem; border-radius: 50px;">Start Secure Trial</a>
        </div>
    </section>

    <?php require_once '../../includes/public_footer.php'; ?>
</body>
</html>
