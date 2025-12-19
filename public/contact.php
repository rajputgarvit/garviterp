<?php
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/Mail.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $marketing = isset($_POST['marketing']) ? 1 : 0;

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $db = Database::getInstance();
            // Save to DB
            $db->query(
                "INSERT INTO contact_requests (name, email, subject, message, marketing_opt_in) VALUES (?, ?, ?, ?, ?)", 
                [$name, $email, $subject, $message, $marketing]
            );

            // Send Confirmation Email
            $mail = new Mail();
            $confirmationBody = "
                <h2>Thank you for contacting us, " . htmlspecialchars($name) . "!</h2>
                <p>We have received your query regarding: <strong>" . htmlspecialchars($subject) . "</strong>.</p>
                <p>Our team will review your message and get back to you shortly.</p>
                <br>
                <p>Best regards,<br>" . APP_NAME . " Team</p>
            ";
            $mail->sendWithResend($email, 'We have received your message - ' . APP_NAME, $confirmationBody, APP_NAME . ' Support', 'support@acculynce.com');
            
            // Notify Admin
            $adminBody = "
                <h3>New Contact Request</h3>
                <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                <p><strong>Marketing Opt-in:</strong> " . ($marketing ? 'Yes' : 'No') . "</p>
                <hr>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
            ";
            $mail->sendWithResend('support@acculynce.com', "New Contact: $subject", $adminBody, 'System Notification', 'no-reply@acculynce.com');

            $success = 'Thank you! Your message has been sent successfully. We will be in touch soon.';
            
            // Clear form
            $name = $email = $subject = $message = '';
            
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again later.';
            error_log($e->getMessage());
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php 
    $pageTitle = "Contact Us";
    $pageDescription = "Get in touch with Acculynce team. We are here to help you modernize your business with our Enterprise ERP solution.";
    require_once __DIR__ . '/../includes/public_meta.php'; 
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <style>
        .contact-hero {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 160px 0 100px;
            text-align: center;
        }
        
        .contact-title {
            font-size: 3rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--primary-color), #4f46e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .contact-subtitle {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        .contact-section {
            padding: 40px 0 80px;
            background: white;
        }

        .contact-card {
            max-width: 800px;
            margin: -80px auto 0;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .form-group { margin-bottom: 24px; }
        
        .form-label {
            font-weight: 500;
            color: #334155;
            margin-bottom: 8px;
            display: block;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s;
            background: white;
            color: #1e293b;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn-submit {
            background: var(--primary-color);
            color: white;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            width: 100%;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .marketing-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 32px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .marketing-checkbox input[type="checkbox"] {
            margin-top: 3px;
            width: 16px;
            height: 16px;
            accent-color: var(--primary-color);
        }

        .marketing-text {
            font-size: 0.9rem;
            color: #475569;
            line-height: 1.5;
        }
        
        .contact-info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            max-width: 1000px;
            margin: 60px auto 0;
        }
        
        .info-box {
            text-align: center;
            padding: 24px;
            background: #f8fafc;
            border-radius: 12px;
            transition: transform 0.2s;
        }

        .info-box:hover {
            transform: translateY(-2px);
        }
        
        .info-icon {
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            font-size: 1.25rem;
        }

        @media (max-width: 768px) {
            .contact-card { padding: 24px; margin-top: -40px; border-radius: 16px; }
            .contact-title { font-size: 2rem; }
            .contact-info-grid { grid-template-columns: 1fr; }
            .contact-hero { padding: 120px 0 80px; }
        }
    </style>
</head>
<body>

<?php include '../includes/public_header.php'; ?>

<div class="contact-hero">
    <div class="container">
        <h1 class="contact-title">Get in Touch</h1>
        <p class="contact-subtitle">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
    </div>
</div>

<div class="contact-section">
    <div class="container">
        
        <div class="contact-card">
            <?php if ($success): ?>
                <div class="alert alert-success" style="background: #d1fae5; color: #065f46; padding: 16px; border-radius: 12px; margin-bottom: 24px; text-align: center;">
                    <i class="fas fa-check-circle margin-right-2"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger" style="background: #fee2e2; color: #991b1b; padding: 16px; border-radius: 12px; margin-bottom: 24px; text-align: center;">
                    <i class="fas fa-exclamation-circle margin-right-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="John Doe" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="john@example.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="How can we help?" value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="6" placeholder="Tell us more about your inquiry..." required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                </div>

                <div class="marketing-checkbox">
                    <input type="checkbox" name="marketing" id="marketing" checked>
                    <div class="marketing-text">
                        <label for="marketing" style="font-weight: 600; display: block; margin-bottom: 4px;">Stay in the loop</label>
                        I want to receive news, feature updates, and special offers via email. You can unsubscribe at any time.
                    </div>
                </div>

                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>

        <div class="contact-info-grid">
            <div class="info-box">
                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                <h3 style="font-weight: 700; margin-bottom: 8px;">Email Us</h3>
                <p style="color: var(--text-secondary);">support@acculynce.com</p>
            </div>
            <div class="info-box">
                <div class="info-icon"><i class="fas fa-phone"></i></div>
                <h3 style="font-weight: 700; margin-bottom: 8px;">Call Us</h3>
                <p style="color: var(--text-secondary);">+1 (555) 123-4567</p>
            </div>
            <div class="info-box">
                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <h3 style="font-weight: 700; margin-bottom: 8px;">Visit Us</h3>
                <p style="color: var(--text-secondary);">123 Business Ave, Tech City</p>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/public_footer.php'; ?>
</body>
</html>
