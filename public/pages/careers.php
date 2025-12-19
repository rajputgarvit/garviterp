<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php 
    $pageTitle = "Careers";
    $pageDescription = "Join the Acculynce team. Help us build the operating system for modern businesses. View open positions.";
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

        .benefits-section {
            padding: 80px 0;
            background: white;
        }

        .section-title {
            text-align: center;
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 60px;
            color: #0f172a;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .benefit-card {
            text-align: center;
            padding: 32px;
            background: #f8fafc;
            border-radius: 20px;
            transition: transform 0.3s;
        }

        .benefit-card:hover {
            transform: translateY(-5px);
        }

        .benefit-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            background: white;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .benefit-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1e293b;
        }

        .benefit-desc {
            color: #64748b;
            line-height: 1.6;
        }

        .jobs-section {
            padding: 80px 0;
            background: #f8fcfd;
        }

        .job-card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
        }

        .job-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }

        .job-info h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #0f172a;
        }

        .job-meta {
            display: flex;
            gap: 16px;
            color: #64748b;
            font-size: 0.95rem;
        }

        .job-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-apply {
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .benefits-grid { grid-template-columns: 1fr; }
            .job-card { flex-direction: column; align-items: flex-start; gap: 20px; }
            .btn-apply { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/public_header.php'; ?>

    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Join Our Team</h1>
            <p class="page-subtitle">Build the future of enterprise software with us. We're a team of dreamers, doers, and relentless problem-solvers.</p>
        </div>
    </header>

    <section class="benefits-section">
        <div class="container">
            <h2 class="section-title">Why Join Acculynce?</h2>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-rocket"></i></div>
                    <div class="benefit-title">High Growth</div>
                    <p class="benefit-desc">Join a fast-growing startup where your work directly impacts company success and trajectory.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-laptop-house"></i></div>
                    <div class="benefit-title">Remote First</div>
                    <p class="benefit-desc">Work from anywhere. We believe in output over hours and provide a budget for your home office.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-heartbeat"></i></div>
                    <div class="benefit-title">Great Benefits</div>
                    <p class="benefit-desc">Comprehensive health insurance, unlimited PTO, and generous equity packages for all employees.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="jobs-section">
        <div class="container">
            <h2 class="section-title">Open Positions</h2>
            
            <div style="font-weight: 600; color: #64748b; margin-bottom: 24px; text-transform: uppercase; font-size: 0.9rem; letter-spacing: 0.05em;">Engineering</div>
            
            <div class="job-card">
                <div class="job-info">
                    <h3>Senior Full Stack Developer</h3>
                    <div class="job-meta">
                        <span><i class="fas fa-map-marker-alt"></i> Remote</span>
                        <span><i class="fas fa-clock"></i> Full-time</span>
                    </div>
                </div>
                <a href="#apply" class="btn btn-outline-primary btn-apply">Apply Now</a>
            </div>

            <div class="job-card">
                <div class="job-info">
                    <h3>DevOps Engineer</h3>
                    <div class="job-meta">
                        <span><i class="fas fa-map-marker-alt"></i> Remote</span>
                        <span><i class="fas fa-clock"></i> Full-time</span>
                    </div>
                </div>
                <a href="#apply" class="btn btn-outline-primary btn-apply">Apply Now</a>
            </div>

            <div style="font-weight: 600; color: #64748b; margin-bottom: 24px; margin-top: 40px; text-transform: uppercase; font-size: 0.9rem; letter-spacing: 0.05em;">Product & Design</div>

            <div class="job-card">
                <div class="job-info">
                    <h3>Product Designer</h3>
                    <div class="job-meta">
                        <span><i class="fas fa-map-marker-alt"></i> New York / Remote</span>
                        <span><i class="fas fa-clock"></i> Full-time</span>
                    </div>
                </div>
                <a href="#apply" class="btn btn-outline-primary btn-apply">Apply Now</a>
            </div>

             <div class="job-card">
                <div class="job-info">
                    <h3>Product Manager</h3>
                    <div class="job-meta">
                        <span><i class="fas fa-map-marker-alt"></i> Remote</span>
                        <span><i class="fas fa-clock"></i> Full-time</span>
                    </div>
                </div>
                <a href="#apply" class="btn btn-outline-primary btn-apply">Apply Now</a>
            </div>

            <div style="text-align: center; margin-top: 60px;">
                <p style="color: #64748b; margin-bottom: 16px;">Don't see a role that fits?</p>
                <a href="../contact.php" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">Contact us directly <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <?php require_once '../../includes/public_footer.php'; ?>
</body>
</html>
