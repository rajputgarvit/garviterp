<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php 
    $pageTitle = "About Us";
    $pageDescription = "Learn about Acculynce, our mission to simplify business operations, and the team behind the platform.";
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

        .story-section {
            padding: 80px 0;
        }

        .story-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .story-content h2 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 24px;
            color: #0f172a;
        }

        .story-content p {
            color: #475569;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 24px;
        }

        .story-image {
            background: #f1f5f9;
            border-radius: 24px;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cbd5e1;
            font-size: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .story-image::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(45deg, rgba(79, 70, 229, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%);
        }

        .values-section {
            background: #0f172a;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin-top: 60px;
        }

        .value-card {
            background: rgba(255,255,255,0.05);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            transition: transform 0.3s;
        }

        .value-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.1);
        }

        .value-icon {
            font-size: 2rem;
            color: #60a5fa;
            margin-bottom: 24px;
        }

        .value-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .value-text {
            color: #94a3b8;
            line-height: 1.6;
        }

        .stats-section {
            padding: 80px 0;
            background: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 1rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        @media (max-width: 900px) {
            .story-container { grid-template-columns: 1fr; }
            .values-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/public_header.php'; ?>

    <header class="page-header">
        <div class="container">
            <h1 class="page-title">We Are Acculynce</h1>
            <p class="page-subtitle">A dedicated team of innovators, engineers, and problem solvers working to simplify business operations for everyone.</p>
        </div>
    </header>

    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div>
                    <div class="stat-number">5k+</div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div>
                    <div class="stat-number">2M+</div>
                    <div class="stat-label">Invoices Sent</div>
                </div>
                <div>
                    <div class="stat-number">99.9%</div>
                    <div class="stat-label">Uptime</div>
                </div>
                <div>
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support</div>
                </div>
            </div>
        </div>
    </section>

    <section class="story-section">
        <div class="container">
            <div class="story-container">
                <div class="story-content">
                    <span style="color: var(--primary-color); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: block;">Our Mission</span>
                    <h2>Empowering businesses to grow without limits</h2>
                    <p>Founded in 2023, Acculynce started with a simple idea: enterprise software shouldn't be so complicated. We saw businesses struggling with outdated, clunky tools that slowed them down instead of speeding them up.</p>
                    <p>We set out to build a platform that combines power with simplicity. A suite of tools that feels cohesive, intuitive, and genuinely helpful. Today, we help thousands of companies streamline their finance, inventory, and HR operations in one unified system.</p>
                    <p>We believe that when you remove operational friction, creativity and growth flourish. That's why we come to work every day.</p>
                </div>
                <div class="story-image">
                    <i class="fas fa-building" style="opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </section>

    <section class="values-section">
        <div class="container">
            <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 20px;">Our Core Values</h2>
            <p style="color: #94a3b8; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">The principles that guide every decision we make.</p>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-lightbulb"></i></div>
                    <h3 class="value-title">Relentless Innovation</h3>
                    <p class="value-text">We never settle for "good enough." We are constantly pushing boundaries to find better, faster ways to solve problems.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-heart"></i></div>
                    <h3 class="value-title">Customer Obsession</h3>
                    <p class="value-text">Our customers are our partners. We listen, learn, and build strictly based on what helps them succeed.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3 class="value-title">Uncompromising Trust</h3>
                    <p class="value-text">We handle critical business data. Innovation means nothing without the security and reliability to back it up.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="story-section" style="background: #f8fafc; text-align: center; padding: 100px 0;">
        <div class="container">
            <h2 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 30px;">Join our journey</h2>
            <p style="max-width: 600px; margin: 0 auto 40px; color: #64748b; font-size: 1.1rem;">We're always looking for talented individuals to join our team, and forward-thinking businesses to partner with.</p>
            <div style="display: flex; gap: 16px; justify-content: center;">
                <a href="careers.php" class="btn btn-outline" style="border: 1px solid #cbd5e1; padding: 14px 32px; border-radius: 50px; font-weight: 600; color: #334155; transition: all 0.2s;">View Careers</a>
                <a href="../contact.php" class="btn btn-primary" style="padding: 14px 32px; border-radius: 50px; font-weight: 600;">Contact Us</a>
            </div>
        </div>
    </section>

    <?php require_once '../../includes/public_footer.php'; ?>
    <script src="../../public/assets/js/landing.js"></script>

</body>
</html>
