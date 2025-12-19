<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php 
    $pageTitle = "Privacy Policy";
    $pageDescription = "Our commitment to protecting your data and privacy. Read the full Privacy Policy of Acculynce.";
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
            <h1 class="legal-title">Privacy Policy</h1>
            <p class="legal-date">Last Updated: <?php echo date('F d, Y'); ?></p>
        </div>
    </header>

    <section class="legal-content">
        <div class="container legal-container">
            <div class="legal-section">
                <p>At Acculynce ("we," "our," or "us"), we are deeply committed to protecting your privacy and ensuring the security of your personal and business information. This Privacy Policy outlines our practices regarding the collection, use, and disclosure of your information when you use our cloud-based ERP software, website, and related services (collectively, the "Service").</p>
                <p>By accessing or using our Service, you agree to the collection and use of information in accordance with this policy. We value your trust and strive to be transparent about how we handle your data.</p>
            </div>

            <div class="legal-section">
                <h2>1. Information We Collect</h2>
                
                <h3>1.1 Personal Information</h3>
                <p>To provide you with our Service, we may ask you to provide us with certain personally identifiable information that can be used to contact or identify you. This includes, but is not limited to:</p>
                <ul>
                    <li><strong>Identity Data:</strong> Full name, username, or similar identifier.</li>
                    <li><strong>Contact Data:</strong> Email address, telephone numbers, and billing address.</li>
                    <li><strong>Professional Data:</strong> Company name, job title, and role within the organization.</li>
                    <li><strong>Payment Data:</strong> Credit card details and billing information (processed securely by our third-party payment processors).</li>
                </ul>

                <h3>1.2 Business Data</h3>
                <p>As an ERP provider, we process data that you input into our system ("Customer Data") to facilitate your business operations. You retain full ownership of your Customer Data. This may include:</p>
                <ul>
                    <li>Employee records (names, identification numbers, salary details, attendance).</li>
                    <li>Financial records (invoices, expenses, bank account details, tax information).</li>
                    <li>Inventory, product, and supply chain data.</li>
                    <li>Customer and supplier databases.</li>
                </ul>
                <p>We process this data strictly for the purpose of providing the agreed-upon Service to you. We do not access this data for any other purpose unless required by law or to provide customer support at your request.</p>

                <h3>1.3 Usage & Technical Data</h3>
                <p>We automatically collect information on how the Service is accessed and used. This "Usage Data" may include:</p>
                <ul>
                    <li>Internet Protocol (IP) address</li>
                    <li>Browser type and version</li>
                    <li>Device information (operating system, unique device identifiers)</li>
                    <li>Pages of our Service that you visit</li>
                    <li>Time and date of your visits</li>
                    <li>Time spent on pages and other diagnostic data</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>2. How We Use Your Information</h2>
                <p>We use the collected data for various business purposes, including:</p>
                <ul>
                    <li><strong>Service Delivery:</strong> To provide, operate, and maintain our ERP platform.</li>
                    <li><strong>billing and Payments:</strong> To process your subscription payments and send invoices.</li>
                    <li><strong>Communication:</strong> To send you administrative information, including security alerts, support messages, and updates about the Service.</li>
                    <li><strong>Support:</strong> To provide customer support and troubleshoot technical issues.</li>
                    <li><strong>Improvement:</strong> To analyze usage patterns and trends to improve user experience and develop new features.</li>
                    <li><strong>Security:</strong> To detect, prevent, and address technical issues and fraudulent activities.</li>
                    <li><strong>Legal Compliance:</strong> To comply with applicable laws, regulations, and legal processes.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>3. Data Sharing and Disclosure</h2>
                <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following specific circumstances:</p>
                <ul>
                    <li><strong>Service Providers:</strong> We may share data with trusted third-party service providers who perform services on our behalf, such as hosting (e.g., AWS), data analysis, payment processing (e.g., Razorpay), and email delivery. These providers are bound by confidentiality clauses and are not permitted to use your information for their own purposes.</li>
                    <li><strong>Legal Requirements:</strong> We may disclose your information if required to do so by law or in the good faith belief that such action is necessary to comply with a legal obligation, protect and defend the rights or property of Acculynce, or protect the personal safety of users of the Service or the public.</li>
                    <li><strong>Business Transfers:</strong> If Acculynce is involved in a merger, acquisition, or asset sale, your Personal Data may be transferred. We will provide notice before your Personal Data becomes subject to a different Privacy Policy.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>4. Data Retention</h2>
                <p>We will retain your Personal Data and Customer Data only for as long as is necessary for the purposes set out in this Privacy Policy. We will retain and use your information to the extent necessary to comply with our legal obligations (for example, if we are required to retain your data to comply with applicable laws), resolve disputes, and enforce our legal agreements and policies.</p>
                <p>Upon termination of your account, we may retain your data for a limited period as part of our backup procedures or as required by law, after which it will be permanently deleted.</p>
            </div>

            <div class="legal-section">
                <h2>5. Data Security</h2>
                <p>The security of your data is of paramount importance to us. We implement robust, industry-standard security measures to protect your information, including:</p>
                <ul>
                    <li><strong>Encryption:</strong> All data transmitted between your browser and our servers is encrypted using SSL/TLS technology. Sensitive data at rest is also encrypted.</li>
                    <li><strong>Access Controls:</strong> We maintain strict physical, electronic, and procedural safeguards to restrict access to your data to authorized personnel only.</li>
                    <li><strong>Regular Audits:</strong> We conduct regular security assessments and vulnerability scans to identify and address potential risks.</li>
                    <li><strong>Backups:</strong> We perform regular data backups to ensure business continuity in the event of a disaster.</li>
                </ul>
                <p>However, please be aware that no method of transmission over the Internet, or method of electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your Personal Data, we cannot guarantee its absolute security.</p>
            </div>

            <div class="legal-section">
                <h2>6. Your Data Rights</h2>
                <p>Depending on your jurisdiction, you may have certain rights regarding your personal information, including:</p>
                <ul>
                    <li><strong>Right to Access:</strong> You have the right to request copies of your personal data.</li>
                    <li><strong>Right to Rectification:</strong> You have the right to request that we correct any information you believe is inaccurate or complete information you believe is incomplete.</li>
                    <li><strong>Right to Erasure:</strong> You have the right to request that we erase your personal data, under certain conditions.</li>
                    <li><strong>Right to Restrict Processing:</strong> You have the right to request that we restrict the processing of your personal data.</li>
                    <li><strong>Right to Data Portability:</strong> You have the right to request that we transfer the data that we have collected to another organization, or directly to you, in a structured, machine-readable format.</li>
                </ul>
                <p>If you wish to exercise any of these rights, please contact us at <a href="mailto:support@acculynce.com">support@acculynce.com</a>.</p>
            </div>

            <div class="legal-section">
                <h2>7. Children's Privacy</h2>
                <p>Our Service is not intended for use by anyone under the age of 18 ("Children"). We do not knowingly collect personally identifiable information from children. If you are a parent or guardian and you are aware that your child has provided us with Personal Data, please contact us. If we become aware that we have collected Personal Data from children without verification of parental consent, we take steps to remove that information from our servers.</p>
            </div>

            <div class="legal-section">
                <h2>8. Changes to This Privacy Policy</h2>
                <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date at the top of this policy. We may also notify you via email or a prominent notice on our Service.</p>
                <p>You are advised to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy are effective when they are posted on this page.</p>
            </div>

            <div class="legal-contact">
                <h2>Contact Us</h2>
                <p>If you have any questions or concerns about this Privacy Policy, please contact us:</p>
                <div style="margin-top: 20px;">
                    <p style="font-size: 1.2rem; margin-bottom: 8px;">
                        <i class="fas fa-envelope" style="color: var(--primary-color); width: 24px;"></i>
                        <a href="mailto:support@acculynce.com" style="color: #1e293b; text-decoration: none; font-weight: 600;">support@acculynce.com</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <?php require_once '../../includes/public_footer.php'; ?>
</body>
</html>
