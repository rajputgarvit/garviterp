<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php 
    $pageTitle = "Blog";
    $pageDescription = "Insights, trends, and tips for modern business management from the Acculynce team.";
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
            <h1 class="page-title">Latest Updates</h1>
            <p class="page-subtitle">Insights, news, and guides from the Acculynce team.</p>
        </div>
    </header>

    <section class="content-section">
        <div class="container">
            <div style="text-align: center; padding: 40px; background: #f1f5f9; border-radius: 16px;">
                 <h3>Recent Posts</h3>
                 <p>Blog layout and articles.</p>
            </div>
        </div>
    </section>

    <?php require_once '../../includes/public_footer.php'; ?>
</body>
</html>
