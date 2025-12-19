<?php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/SupportManager.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getCurrentUser();

$supportManager = new SupportManager();
$categories = $supportManager->getCategories();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $categoryId = $_POST['category_id'] ?? '';
    $priority = $_POST['priority'] ?? 'Medium';
    $message = trim($_POST['message'] ?? '');
    
    // Basic Validation
    if (empty($subject) || empty($categoryId) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Create Ticket
        try {
            $ticketId = $supportManager->createTicket($user['id'], $categoryId, $subject, $message, $priority);
            if ($ticketId) {
                header("Location: index.php?msg=created"); // Simple redirect for now
                exit;
            } else {
                $error = 'Failed to create ticket. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        .ticket-form-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border-color);
        }

        .info-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            height: fit-content;
        }

        .info-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-list li {
            margin-bottom: 12px;
            display: flex;
            gap: 10px;
            font-size: 0.95rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .info-list li i {
            color: var(--primary-color);
            margin-top: 4px;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 12px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color), #4f46e5);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: opacity 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .page-container {
                grid-template-columns: 1fr;
            }
        }

        /* Modern Header Styles */
        .page-header-modern {
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .breadcrumb-nav {
            margin-bottom: 8px;
        }

        .breadcrumb-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 8px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .breadcrumb-list li {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .breadcrumb-list li:not(:last-child)::after {
            content: "/";
            color: #cbd5e1;
        }

        .breadcrumb-link {
            text-decoration: none;
            color: var(--text-secondary);
            transition: color 0.2s;
        }

        .breadcrumb-link:hover {
            color: var(--primary-color);
        }

        .breadcrumb-current {
            color: var(--text-primary);
            font-weight: 500;
        }

        .header-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 8px 0;
            line-height: 1.2;
        }

        .header-subtitle {
            font-size: 1rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .btn-view-tickets {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .btn-view-tickets:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include INCLUDES_PATH . '/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include INCLUDES_PATH . '/header.php'; ?>
            
            <div class="content-area">
                <div class="page-header-modern">
                    <div>
                        <nav class="breadcrumb-nav">
                            <ul class="breadcrumb-list">
                                <li>
                                    <a href="index.php" class="breadcrumb-link">Support</a>
                                </li>
                                <li>
                                    <span class="breadcrumb-current">Create Ticket</span>
                                </li>
                            </ul>
                        </nav>
                        <h1 class="header-title">Submit a Support Request</h1>
                        <p class="header-subtitle">Fill out the form below to get assistance from our support team.</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn-view-tickets">
                            <i class="fas fa-list-ul"></i>
                            <span>View My Tickets</span>
                        </a>
                    </div>
                </div>

                <div class="page-container">
                    <!-- Left Column: Form -->
                    <div class="ticket-form-card">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" style="margin-bottom: 24px; display: flex; gap: 10px; align-items: center;">
                                <i class="fas fa-exclamation-circle fa-lg"></i>
                                <span><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group" style="margin-bottom: 24px;">
                                <label class="form-label required">Subject</label>
                                <input type="text" name="subject" class="form-control" placeholder="E.g., Unable to generate invoice for Order #123" required>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                                <div class="form-group">
                                    <label class="form-label required">Category</label>
                                    <select name="category_id" class="form-control" required style="appearance: auto;">
                                        <option value="">Select a category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Priority</label>
                                    <select name="priority" class="form-control" style="appearance: auto;">
                                        <option value="Low">Low - General Inquiry</option>
                                        <option value="Medium" selected>Medium - Normal Priority</option>
                                        <option value="High">High - Urgent Issue</option>
                                        <option value="Critical">Critical - Blocker</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 32px;">
                                <label class="form-label required">Description</label>
                                <textarea name="message" class="form-control" rows="10" placeholder="Please describe the issue. Include steps to reproduce, expected behavior, and any relevant details..." required></textarea>
                            </div>

                            <div style="display: flex; gap: 16px; align-items: center;">
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-paper-plane"></i> Submit Ticket
                                </button>
                                <a href="index.php" style="color: var(--text-secondary); text-decoration: none; font-weight: 500;">Cancel</a>
                            </div>
                        </form>
                    </div>

                    <!-- Right Column: Info/Tips -->
                    <div class="info-card">
                        <div class="info-title">
                            <i class="fas fa-lightbulb" style="color: #fbbf24;"></i>
                            Before you submit
                        </div>
                        <ul class="info-list">
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><strong>Check the Knowledge Base:</strong> Many common issues are explained in our documentation.</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><strong>Provide Details:</strong> The more information you provide (screenshots, error codes), the faster we can help.</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><strong>One Issue per Ticket:</strong> Please create separate tickets for different problems to ensure efficient tracking.</span>
                            </li>
                        </ul>
                        
                        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
                            <div class="info-title" style="font-size: 1rem;">
                                <i class="fas fa-headset" style="color: var(--primary-color);"></i>
                                Immediate Help?
                            </div>
                            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0;">
                                For critical system outages, please contact the emergency hotline at <strong>+91 9520447284</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
