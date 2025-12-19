<?php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/SupportManager.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getCurrentUser();

$supportManager = new SupportManager();

$ticketId = $_GET['id'] ?? 0;
// We'll pass the ID. To verify access, getDetails will be used.
$ticket = $supportManager->getTicketDetails($ticketId);

// Access Control
if (!$ticket) {
    die("Ticket not found.");
}

$canManage = $auth->hasRole('Super Admin') || $auth->hasRole('Admin');
if ($ticket['user_id'] != $user['id'] && !$canManage) {
    die("Access Denied.");
}

// Handle Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reply_message'])) {
        $message = trim($_POST['reply_message']);
        if (!empty($message)) {
            $supportManager->addReply($ticket['id'], $user['id'], $message);
            header("Location: view.php?id=" . $ticket['id']);
            exit;
        }
    }
    
    // Handle Status Change (Admin/Owner)
    if (isset($_POST['update_status'])) {
        $newStatus = $_POST['status'];
        $supportManager->updateStatus($ticket['id'], $newStatus);
        header("Location: view.php?id=" . $ticket['id']);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?php echo htmlspecialchars($ticket['ticket_number']); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Shared Modern UI Styles */
        .page-header-modern {
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .breadcrumb-nav { margin-bottom: 8px; }
        .breadcrumb-list {
            list-style: none; padding: 0; margin: 0; display: flex; gap: 8px;
            font-size: 0.85rem; color: var(--text-secondary);
        }
        .breadcrumb-list li { display: flex; align-items: center; gap: 8px; }
        .breadcrumb-list li:not(:last-child)::after { content: "/"; color: #cbd5e1; }
        .breadcrumb-link { text-decoration: none; color: var(--text-secondary); transition: color 0.2s; }
        .breadcrumb-link:hover { color: var(--primary-color); }
        .breadcrumb-current { color: var(--text-primary); font-weight: 500; }
        .header-title {
            font-size: 2rem; font-weight: 700; color: var(--text-primary);
            margin: 0; line-height: 1.2;
        }

        /* Ticket Meta & Status */
        .ticket-overview-card {
            background: white; border-radius: 16px; padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
        }
        .ticket-header-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; }
        .ticket-subject { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 12px; }
        .status-badge-lg {
            padding: 6px 16px; border-radius: 20px; font-weight: 600; font-size: 0.9rem;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .status-Open { background-color: #d1fae5; color: #065f46; }
        .status-In-Progress { background-color: #dbeafe; color: #1e40af; }
        .status-Awaiting-Reply { background-color: #ffedd5; color: #9a3412; }
        .status-Resolved { background-color: #f3f4f6; color: #374151; }
        .status-Closed { background-color: #f1f5f9; color: #64748b; }

        .ticket-meta-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;
            padding-top: 20px; margin-top: 20px; border-top: 1px solid var(--border-color);
        }
        .meta-box { display: flex; flex-direction: column; gap: 4px; }
        .meta-label { font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .meta-value { font-size: 0.95rem; color: var(--text-primary); font-weight: 500; display: flex; align-items: center; gap: 8px; }

        /* Conversation */
        .conversation-container { max-width: 900px; margin: 0 auto; }
        .message-card {
            background: white; border-radius: 12px; border: 1px solid var(--border-color);
            margin-bottom: 24px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .message-card.staff-reply { border: 1px solid #bfdbfe; background: #eff6ff; }
        .message-card.user-reply { border-left: 4px solid var(--primary-color); }
        .message-header {
            padding: 16px 24px; background: rgba(255,255,255,0.5);
            border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center;
        }
        .sender-name { font-weight: 600; color: var(--text-primary); font-size: 1rem; }
        .sender-role { font-size: 0.75rem; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; font-weight: 700; margin-left: 8px; }
        .role-staff { background: #dbeafe; color: #1e40af; }
        .role-user { background: #f1f5f9; color: #64748b; }
        .message-time { font-size: 0.85rem; color: var(--text-secondary); }
        .message-body { padding: 24px; color: var(--text-primary); line-height: 1.6; font-size: 1rem; }

        .reply-section { background: white; border-radius: 16px; padding: 24px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-top: 40px; }
        .btn-action { padding: 10px 20px; border-radius: 8px; font-weight: 600; transition: all 0.2s; cursor: pointer; border: none; }
        .btn-primary-action { background: var(--primary-color); color: white; }
        .btn-primary-action:hover { opacity: 0.9; transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include INCLUDES_PATH . '/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include INCLUDES_PATH . '/header.php'; ?>
            
            <div class="content-area">
                <!-- Breadcrumb & Header -->
                <div class="page-header-modern">
                    <div>
                        <nav class="breadcrumb-nav">
                            <ul class="breadcrumb-list">
                                <li><a href="index.php" class="breadcrumb-link">Support</a></li>
                                <li><a href="index.php" class="breadcrumb-link">My Tickets</a></li>
                                <li><span class="breadcrumb-current">#<?php echo htmlspecialchars($ticket['ticket_number']); ?></span></li>
                            </ul>
                        </nav>
                        <h1 class="header-title">Ticket Details</h1>
                    </div>
                    <div>
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="update_status" value="1">
                            <?php if ($ticket['status'] !== 'Closed'): ?>
                                <input type="hidden" name="status" value="Closed">
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to close this ticket?')">
                                    <i class="fas fa-check-circle"></i> Close Ticket
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="status" value="Open">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo"></i> Re-open Ticket
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Ticket Overview -->
                <div class="ticket-overview-card">
                    <div class="ticket-header-row">
                        <div style="flex: 1;">
                            <h2 class="ticket-subject"><?php echo htmlspecialchars($ticket['subject']); ?></h2>
                            <div style="display: flex; gap: 12px; align-items: center;">
                                <span class="status-badge-lg status-<?php echo str_replace(' ', '-', $ticket['status']); ?>">
                                    <i class="fas fa-circle" style="font-size: 8px;"></i> <?php echo htmlspecialchars($ticket['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="ticket-meta-grid">
                        <div class="meta-box">
                            <span class="meta-label">Category</span>
                            <span class="meta-value"><i class="fas fa-folder text-muted"></i> <?php echo htmlspecialchars($ticket['category_name']); ?></span>
                        </div>
                        <div class="meta-box">
                            <span class="meta-label">Priority</span>
                            <?php 
                                $pColor = $ticket['priority'] === 'High' || $ticket['priority'] === 'Critical' ? '#ef4444' : ($ticket['priority'] === 'Medium' ? '#f59e0b' : '#10b981');
                            ?>
                            <span class="meta-value" style="color: <?php echo $pColor; ?>;"><i class="fas fa-flag"></i> <?php echo htmlspecialchars($ticket['priority']); ?></span>
                        </div>
                        <div class="meta-box">
                            <span class="meta-label">Created on</span>
                            <span class="meta-value"><i class="far fa-calendar text-muted"></i> <?php echo date('M d, Y h:i A', strtotime($ticket['created_at'])); ?></span>
                        </div>
                        <div class="meta-box">
                            <span class="meta-label">Ticket ID</span>
                            <span class="meta-value">#<?php echo htmlspecialchars($ticket['ticket_number']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Conversation -->
                <div class="conversation-container">
                    <?php foreach ($ticket['replies'] as $reply): ?>
                        <?php 
                            $roles = $reply['replier_roles'] ?? '';
                            $isStaff = (strpos($roles, 'Super Admin') !== false || strpos($roles, 'Admin') !== false); 
                            $cardClass = $isStaff ? 'staff-reply' : 'user-reply';
                        ?>
                        <div class="message-card <?php echo $cardClass; ?>">
                            <div class="message-header">
                                <div style="display: flex; align-items: center;">
                                    <?php if ($isStaff): ?>
                                        <div style="width: 36px; height: 36px; background: white; color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid #dbeafe; margin-right: 12px;">
                                            <i class="fas fa-user-shield"></i>
                                        </div>
                                    <?php else: ?>
                                        <div style="width: 36px; height: 36px; background: #f1f5f9; color: #64748b; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <span class="sender-name"><?php echo htmlspecialchars($reply['replier_name']); ?></span>
                                        <?php if ($isStaff): ?>
                                            <span class="sender-role role-staff">Support Staff</span>
                                        <?php else: ?>
                                            <span class="sender-role role-user">You</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="message-time"><?php echo date('M d, h:i A', strtotime($reply['created_at'])); ?></span>
                            </div>
                            <div class="message-body">
                                <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Reply Section -->
                    <?php if ($ticket['status'] !== 'Closed'): ?>
                        <div class="reply-section">
                            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 20px; color: var(--text-primary);">Post a Reply</h3>
                            <form method="POST" action="">
                                <div class="form-group" style="margin-bottom: 20px;">
                                    <textarea name="reply_message" class="form-control" rows="5" placeholder="Type your message here... Provide as much detail as possible to help us resolve the issue." required style="padding: 16px; border-radius: 12px; font-size: 1rem;"></textarea>
                                </div>
                                <div style="display: flex; justify-content: flex-end;">
                                    <button type="submit" class="btn-action btn-primary-action">
                                        <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; margin-top: 40px; color: var(--text-secondary);">
                            <div style="margin-bottom: 10px; font-size: 2rem; color: #94a3b8;"><i class="fas fa-lock"></i></div>
                            <h4 style="color: var(--text-primary); font-weight: 600;">This ticket is closed</h4>
                            <p>You cannot reply to this ticket unless it is re-opened.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
</body>
</html>
