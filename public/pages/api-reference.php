<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php 
    $pageTitle = "API Reference";
    $pageDescription = "Developer API documentation for Acculynce. Seamlessly integrate your custom business applications with our enterprise ERP platform.";
    require_once '../../includes/public_meta.php'; 
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/landing.css">
    <style>
        .page-header {
            padding: 120px 0 60px;
            text-align: center;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }
        .page-title {
            font-size: 3rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 20px;
        }
        .page-subtitle {
            font-size: 1.25rem;
            color: #64748b;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .content-section {
            padding: 80px 0;
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/public_header.php'; ?>

    <header class="page-header">
        <div class="container">
            <h1 class="page-title">API Reference</h1>
            <p class="page-subtitle">Build integrations and extend Acculynce functionality with our REST API.</p>
        </div>
    </header>

    <section class="content-section">
        <div class="container">
            <div style="text-align: center; padding: 40px; background: #f1f5f9; border-radius: 16px;">
                 <h3>API Endpoints</h3>
                 <p>Authentication, Users, Invoices, and more.</p>
            </div>
        </div>
    </section>

    <?php require_once '../../includes/public_footer.php'; ?>
        <script src="../../public/assets/js/landing.js"></script>

</body>
</html>
