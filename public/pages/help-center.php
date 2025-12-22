<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php 
    $pageTitle = "Help Center";
    $pageDescription = "Get help with Acculynce. Find FAQs, guides, and support resources.";
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

        .search-box-container {
            max-width: 600px;
            margin: 40px auto 0;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 16px 24px 16px 50px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            font-size: 1.1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .faq-section {
            padding: 80px 0;
            background: white;
        }

        .faq-grid {
            max-width: 800px;
            margin: 0 auto;
            display: grid;
            gap: 24px;
        }

        .faq-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .faq-card:hover {
            transform: translateY(-2px);
        }

        .faq-question {
            font-weight: 700;
            font-size: 1.1rem;
            color: #0f172a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-answer {
            color: #64748b;
            margin-top: 12px;
            line-height: 1.6;
            display: none; /* In a real interactive page, JS would toggle this */
        }
        
        /* Simulating one open answer for design */
        .faq-card.open .faq-answer { display: block; }

        .support-options {
            padding: 80px 0;
            background: #f1f5f9;
        }

        .support-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            max-width: 900px;
            margin: 0 auto;
        }

        .support-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        
        .support-icon {
            width: 64px;
            height: 64px;
            background: #eff6ff;
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 24px;
        }
        
        .support-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #0f172a;
        }
        
        .support-card p {
            color: #64748b;
            margin-bottom: 24px;
        }

        @media (max-width: 768px) {
            .support-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/public_header.php'; ?>

    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Help Center</h1>
            <p class="page-subtitle">Find answers to common questions, troubleshoot issues, and get in touch with our support team.</p>
            
            <div class="search-box-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="How can we help you today?">
            </div>
        </div>
    </header>

    <section class="faq-section">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 40px; font-weight: 800; color: #0f172a;">Frequently Asked Questions</h2>
            <div class="faq-grid">
                <div class="faq-card open">
                    <div class="faq-question">
                        How do I reset my password?
                        <i class="fas fa-chevron-down" style="font-size: 0.9rem; color: #94a3b8;"></i>
                    </div>
                    <div class="faq-answer">
                        You can reset your password by clicking on the "Forgot Password" link on the login page. Enter your email address, and we'll send you a secure link to create a new password.
                    </div>
                </div>

                <div class="faq-card">
                    <div class="faq-question">
                        Can I upgrade my plan later?
                        <i class="fas fa-chevron-right" style="font-size: 0.9rem; color: #94a3b8;"></i>
                    </div>
                    <div class="faq-answer">
                        Yes, you can upgrade or downgrade your plan at any time from your account settings. Changes take effect immediately, and prorated charges will apply.
                    </div>
                </div>

                <div class="faq-card">
                    <div class="faq-question">
                        How do I add new team members?
                        <i class="fas fa-chevron-right" style="font-size: 0.9rem; color: #94a3b8;"></i>
                    </div>
                    <div class="faq-answer">
                         You can invite new team members from the "Users & Permissions" section in your dashboard settings. You can assign specific roles to control their access levels.
                    </div>
                </div>

                <div class="faq-card">
                    <div class="faq-question">
                        Is my data secure?
                        <i class="fas fa-chevron-right" style="font-size: 0.9rem; color: #94a3b8;"></i>
                    </div>
                    <div class="faq-answer">
                        Absolutely. We use bank-grade AES-256 encryption for data at rest and TLS 1.3 for data in transit. We conduct regular security audits and are GDPR compliant.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="support-options">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; font-weight: 800;">Still need help?</h2>
            <div class="support-grid">
                <div class="support-card">
                    <div class="support-icon"><i class="fas fa-envelope"></i></div>
                    <h3>Contact Support</h3>
                    <p>Send us a message and we'll get back to you within 24 hours.</p>
                    <a href="../contact.php" class="btn btn-primary" style="padding: 12px 30px; border-radius: 50px;">Send Message</a>
                </div>
                
                <div class="support-card">
                    <div class="support-icon"><i class="fas fa-ticket-alt"></i></div>
                    <h3>Submit a Ticket</h3>
                    <p>Log in to your account to submit and track support tickets.</p>
                    <a href="../../modules/auth/login.php" class="btn btn-outline" style="border: 2px solid #e2e8f0; padding: 12px 30px; border-radius: 50px; font-weight: 600; color: #475569;">Log In & Submit</a>
                </div>
            </div>
        </div>
    </section>

    <?php require_once '../../includes/public_footer.php'; ?>
    
    <script>
        document.querySelectorAll('.faq-card').forEach(card => {
            card.addEventListener('click', () => {
                // Close other cards
                document.querySelectorAll('.faq-card').forEach(c => {
                    if (c !== card) {
                        c.classList.remove('open');
                        const icon = c.querySelector('.fa-chevron-down');
                        if (icon) {
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-right');
                        }
                    }
                });

                // Toggle current card
                card.classList.toggle('open');
                
                // Toggle icon
                const icon = card.querySelector('i');
                if (card.classList.contains('open')) {
                    icon.classList.remove('fa-chevron-right');
                    icon.classList.add('fa-chevron-down');
                } else {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-right');
                }
            });
        });
    </script>
        <script src="../../public/assets/js/landing.js"></script>

</body>
</html>
