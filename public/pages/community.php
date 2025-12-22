<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php 
    $pageTitle = "Community";
    $pageDescription = "Join the Acculynce community. Connect with other business leaders, developers, and experts using our platform.";
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
        
        .community-section {
            padding: 80px 0;
            background: white;
        }

        .forum-categories {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 32px;
            margin-bottom: 80px;
        }

        .category-card {
            display: flex;
            align-items: flex-start;
            gap: 24px;
            padding: 32px;
            border-radius: 16px;
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .category-card:hover {
            box-shadow: 0 10px 20px -5px rgba(0,0,0,0.05);
            border-color: var(--primary-color);
        }

        .category-icon {
            width: 56px;
            height: 56px;
            background: #f1f5f9;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary-color);
            flex-shrink: 0;
        }

        .category-content h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #0f172a;
        }

        .category-content p {
            color: #64748b;
            line-height: 1.5;
            font-size: 0.95rem;
        }

        .social-connect {
            text-align: center;
            padding: 80px 0;
            background: #f8fafc;
        }

        .social-grid {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .social-card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            width: 280px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            text-decoration: none;
        }

        .social-card:hover {
            transform: translateY(-5px);
        }

        .social-icon {
            font-size: 2.5rem;
            margin-bottom: 16px;
            display: block;
        }

        .discord { color: #5865F2; }
        .twitter { color: #1DA1F2; }
        .github { color: #333; }
        
        .social-card h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .social-card span {
            color: #64748b;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .forum-categories { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/public_header.php'; ?>

    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Community</h1>
            <p class="page-subtitle">Connect with thousands of developers, founders, and experts using Acculynce to build their businesses.</p>
        </div>
    </header>

    <section class="community-section">
        <div class="container">
            <h2 style="font-weight: 800; font-size: 2rem; margin-bottom: 40px;">Discussion Forums</h2>
            
            <div class="forum-categories">
                <a href="#" class="category-card">
                    <div class="category-icon" style="color: #ef4444; background: #fee2e2;"><i class="fas fa-fire"></i></div>
                    <div class="category-content">
                        <h3>Announcements</h3>
                        <p>Latest news, product updates, and release notes from the Acculynce team.</p>
                    </div>
                </a>

                <a href="#" class="category-card">
                    <div class="category-icon" style="color: #3b82f6; background: #dbeafe;"><i class="fas fa-code"></i></div>
                    <div class="category-content">
                        <h3>Developers</h3>
                        <p>Discuss API integration, webhooks, and custom development with peers.</p>
                    </div>
                </a>

                <a href="#" class="category-card">
                    <div class="category-icon" style="color: #10b981; background: #d1fae5;"><i class="fas fa-lightbulb"></i></div>
                    <div class="category-content">
                        <h3>Feature Requests</h3>
                        <p>Have an idea? Share it here and vote on features you want to see next.</p>
                    </div>
                </a>

                <a href="#" class="category-card">
                    <div class="category-icon" style="color: #8b5cf6; background: #ede9fe;"><i class="fas fa-comments"></i></div>
                    <div class="category-content">
                        <h3>General Discussion</h3>
                        <p>Talk about anything related to business operations, finance, or HR.</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <section class="social-connect">
        <div class="container">
            <h2 style="font-weight: 800; font-size: 2rem;">Connect with us</h2>
            <p style="color: #64748b; margin-top: 12px;">Join us on your favorite platforms.</p>
            
            <div class="social-grid">
                <a href="#" class="social-card">
                    <i class="fab fa-discord social-icon discord"></i>
                    <h4>Discord Server</h4>
                    <span>Join 5,000+ members</span>
                </a>
                
                <a href="#" class="social-card">
                    <i class="fab fa-twitter social-icon twitter"></i>
                    <h4>Twitter / X</h4>
                    <span>Follow for updates</span>
                </a>
                
                <a href="#" class="social-card">
                    <i class="fab fa-github social-icon github"></i>
                    <h4>GitHub</h4>
                    <span>Star our repo</span>
                </a>
            </div>
        </div>
    </section>

    <?php require_once '../../includes/public_footer.php'; ?>
        <script src="../../public/assets/js/landing.js"></script>

</body>
</html>
