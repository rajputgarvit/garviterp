<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php 
    $pageTitle = "Terms of Service";
    $pageDescription = "The terms and conditions for using Acculynce services.";
    require_once '../../includes/public_meta.php'; 
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/landing.css">
    <style>
        .legal-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 160px 0 80px;
            text-align: center;
        }
        .legal-title {
            font-size: 3rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--primary-color), #4f46e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .legal-date {
            color: #64748b;
            font-size: 1.1rem;
        }
        .legal-content {
            padding: 80px 0;
            background: #fff;
        }
        .legal-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .legal-section {
            margin-bottom: 48px;
        }
        .legal-section h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        .legal-section h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1e293b;
            margin: 32px 0 16px;
        }
        .legal-section p {
            color: #475569;
            line-height: 1.8;
            font-size: 1.05rem;
            margin-bottom: 16px;
        }
        .legal-section ul {
            margin-bottom: 24px;
            padding-left: 24px;
        }
        .legal-section li {
            color: #475569;
            line-height: 1.8;
            margin-bottom: 8px;
        }
        .legal-contact {
            background: #f8fafc;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            margin-top: 60px;
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/public_header.php'; ?>

    <header class="legal-header">
        <div class="container">
            <h1 class="legal-title">Terms of Service</h1>
            <p class="legal-date">Last Updated: <?php echo date('F d, Y'); ?></p>
        </div>
    </header>

    <section class="legal-content">
        <div class="container legal-container">
            <div class="legal-section">
                <p>Please read these Terms of Service ("Terms", "Terms of Service") carefully before using the Acculynce website and cloud-based ERP application (the "Service") operated by Acculynce Inc. ("us", "we", or "our").</p>
                <p>Your access to and use of the Service is conditioned on your acceptance of and compliance with these Terms. These Terms apply to all visitors, users, and others who access or use the Service.</p>
                <p>By accessing or using the Service you agree to be bound by these Terms. If you disagree with any part of the terms, then you may not access the Service.</p>
            </div>

            <div class="legal-section">
                <h2>1. Accounts</h2>
                <p>When you create an account with us, you must provide us information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms, which may result in immediate termination of your account on our Service.</p>
                <p>You are responsible for safeguarding the password that you use to access the Service and for any activities or actions under your password, whether your password is with our Service or a third-party service. You agree not to disclose your password to any third party. You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.</p>
            </div>

            <div class="legal-section">
                <h2>2. Subscriptions and Payments</h2>
                
                <h3>2.1 Free Trial</h3>
                <p>We may, at our sole discretion, offer a Subscription with a free trial for a limited period of time (typically 14 days). You may be required to enter your billing information in order to sign up for the free trial.</p>
                <p>If you do enter your billing information when signing up for a free trial, you will not be charged by Acculynce until the free trial has expired. On the last day of the free trial period, unless you cancelled your Subscription, you will be automatically charged the applicable Subscription fees for the type of Subscription you have selected.</p>

                <h3>2.2 Billing</h3>
                <p>The Service is billed on a subscription basis ("Subscription(s)"). You will be billed in advance on a recurring and periodic basis ("Billing Cycle"). Billing cycles are set either on a monthly or annual basis, depending on the type of subscription plan you select when purchasing a Subscription.</p>
                <p>At the end of each Billing Cycle, your Subscription will automatically renew under the exact same conditions unless you cancel it or Acculynce cancels it. You may cancel your Subscription renewal either through your online account management page or by contacting Acculynce customer support team.</p>

                <h3>2.3 Refunds</h3>
                <p>Certain refund requests for Subscriptions may be considered by Acculynce on a case-by-case basis and granted at the sole discretion of Acculynce.</p>
            </div>

            <div class="legal-section">
                <h2>3. Acceptable Use Policy</h2>
                <p>You agree not to use the Service:</p>
                <ul>
                    <li>In any way that violates any applicable national or international law or regulation.</li>
                    <li>To transmit, or procure the sending of, any advertising or promotional material, including any "junk mail", "chain letter," "spam," or any other similar solicitation.</li>
                    <li>To impersonate or attempt to impersonate Acculynce, a Acculynce employee, another user, or any other person or entity.</li>
                    <li>To upload or transmit viruses, Trojan horses, or any other type of malicious code that will or may be used in any way that will affect the functionality or operation of the Service.</li>
                    <li>To collect or track the personal information of others to spam, phish, pharm, pretext, spider, crawl, or scrape.</li>
                </ul>
                <p>We reserve the right to terminate your use of the Service for violating any of the prohibited uses.</p>
            </div>

            <div class="legal-section">
                <h2>4. Intellectual Property</h2>
                <p>The Service and its original content (excluding Content provided by users), features, and functionality are and will remain the exclusive property of Acculynce and its licensors. The Service is protected by copyright, trademark, and other laws of both India and foreign countries. Our trademarks and trade dress may not be used in connection with any product or service without the prior written consent of Acculynce.</p>
                <p>You retain all rights to the data, information, and content you upload to the Service ("User Content"). By uploading User Content, you grant us a license to use, store, and copy that content solely for the purpose of providing the Service to you.</p>
            </div>

            <div class="legal-section">
                <h2>5. Termination</h2>
                <p>We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>
                <p>Upon termination, your right to use the Service will immediately cease. If you wish to terminate your account, you may simply discontinue using the Service or contact support to request account deletion.</p>
            </div>

            <div class="legal-section">
                <h2>6. Limitation of Liability</h2>
                <p>In no event shall Acculynce, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from (i) your access to or use of or inability to access or use the Service; (ii) any conduct or content of any third party on the Service; (iii) any content obtained from the Service; and (iv) unauthorized access, use or alteration of your transmissions or content, whether based on warranty, contract, tort (including negligence) or any other legal theory, whether or not we have been informed of the possibility of such damage.</p>
            </div>

            <div class="legal-section">
                <h2>7. Governing Law</h2>
                <p>These Terms shall be governed and construed in accordance with the laws of India, without regard to its conflict of law provisions.</p>
                <p>Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights. If any provision of these Terms is held to be invalid or unenforceable by a court, the remaining provisions of these Terms will remain in effect.</p>
            </div>

            <div class="legal-section">
                <h2>8. Changes to Terms</h2>
                <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material we will try to provide at least 30 days notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.</p>
                <p>By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms. If you do not agree to the new terms, you are authorized to stop using the Service.</p>
            </div>

            <div class="legal-contact">
                <h2>Contact Us</h2>
                <p>If you have any questions about these Terms, please contact us:</p>
                <div style="margin-top: 20px;">
                    <p style="font-size: 1.2rem; margin-bottom: 8px;">
                        <i class="fas fa-envelope" style="color: var(--primary-color); width: 24px;"></i>
                        <a href="mailto:support@acculynce.com" style="color: #1e293b; text-decoration: none; font-weight: 600;">support@acculynce.com</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
    <script src="../../public/assets/js/landing.js"></script>

    <?php require_once '../../includes/public_footer.php'; ?>
</body>
</html>
