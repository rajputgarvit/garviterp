<?php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/SupportManager.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getCurrentUser();

$supportManager = new SupportManager();

$ticketId = $_GET['id'] ?? 0;
// Fetch ticket details (accepts ID or Ticket Number)
$ticket = $supportManager->getTicketDetails($ticketId);

// Access Control
if (!$ticket) {
    die("Ticket not found.");
}

$canManage = $auth->hasRole('Super Admin') || $auth->hasRole('Admin');
if ($ticket['user_id'] != $user['id'] && !$canManage) {
    die("Access Denied.");
}

// Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reply_message'])) {
        $message = trim($_POST['reply_message']);
        if (!empty($message)) {
            $supportManager->addReply($ticket['id'], $user['id'], $message);
            header("Location: view.php?id=" . $ticket['ticket_number']);
            exit;
        }
    }
    
    // Handle Status Change (User closing/reopening own ticket)
    if (isset($_POST['update_status'])) {
        $newStatus = $_POST['status'];
        // Allow user to Close or Reopen only
        if ($newStatus === 'Closed' || $newStatus === 'Open') {
            $supportManager->updateStatus($ticket['id'], $newStatus);
        }
        header("Location: view.php?id=" . $ticket['ticket_number']);
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
    <!-- Add Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Main Style -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Conversation Specific Styles */
        .chat-bubble {
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .avatar-circle { font-family: 'Inter', sans-serif; letter-spacing: -0.5px; }

        /* Shared Badge Styles */
        .bg-success-soft { background-color: #d1e7dd; color: #0f5132; }
        .bg-primary-soft { background-color: #cfe2ff; color: #084298; }
        .bg-warning-soft { background-color: #fff3cd; color: #664d03; }
        .bg-secondary-soft { background-color: #e2e3e5; color: #41464b; }

        /* Scrollbar */
        #conversationBody::-webkit-scrollbar { width: 6px; }
        #conversationBody::-webkit-scrollbar-track { background: transparent; }
        #conversationBody::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.1); border-radius: 3px; }
        #conversationBody:hover::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.2); }

        .hover-lift:hover { transform: translateY(-2px); transition: transform 0.2s; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include INCLUDES_PATH . '/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include INCLUDES_PATH . '/header.php'; ?>
            
            <div class="content-area">
                <!-- Header -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                    <div>
                        <nav aria-label="breadcrumb" class="mb-2">
                            <ol class="breadcrumb mb-0 small">
                                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">My Tickets</a></li>
                                <li class="breadcrumb-item active" aria-current="page">#<?php echo htmlspecialchars($ticket['ticket_number']); ?></li>
                            </ol>
                        </nav>
                        <h1 class="h3 mb-0 fw-bold text-dark">
                            <?php echo htmlspecialchars($ticket['subject']); ?>
                        </h1>
                    </div>
                    
                    <div class="d-flex gap-2 align-items-center">
                        <?php
                        $sClass = match($ticket['status']) {
                            'Open' => 'success',
                            'In Progress' => 'primary',
                            'Awaiting Reply' => 'warning',
                            'Resolved', 'Closed' => 'secondary',
                            default => 'light'
                        };
                        ?>
                        <span class="badge bg-<?php echo $sClass; ?> bg-opacity-10 text-<?php echo $sClass; ?> border border-<?php echo $sClass; ?> border-opacity-10 py-2 px-3">
                            <i class="fas fa-circle me-1" style="font-size: 6px; vertical-align: middle;"></i> <?php echo htmlspecialchars($ticket['status']); ?>
                        </span>

                        <form method="POST" class="d-inline">
                            <input type="hidden" name="update_status" value="1">
                            <?php if ($ticket['status'] !== 'Closed'): ?>
                                <input type="hidden" name="status" value="Closed">
                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to close this ticket? You will not be able to reply unless it is reopened.')">
                                    <i class="fas fa-check-circle me-1"></i> Close Ticket
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="status" value="Open">
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-undo me-1"></i> Re-open Ticket
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Conversation Column -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-comments me-2 text-primary"></i>Conversation</h6>
                                <span class="badge bg-light text-secondary border"><?php echo count($ticket['replies']); ?> Messages</span>
                            </div>
                            
                            <div class="card-body bg-light bg-opacity-50 p-4" style="min-height: 400px; max-height: 600px; overflow-y: auto;" id="conversationBody">
                                <?php if (empty($ticket['replies'])): ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-comment-dots fa-3x opacity-25 mb-3"></i>
                                        <p>No messages yet.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($ticket['replies'] as $reply): ?>
                                        <?php 
                                            // Determine message owner
                                            $isMe = ($reply['user_id'] == $user['id']);
                                            $roles = $reply['replier_roles'] ?? '';
                                            $isStaff = (strpos($roles, 'Super Admin') !== false || strpos($roles, 'Admin') !== false); 
                                            
                                            // Layout variables
                                            // Me = Right, Staff/Other = Left
                                            $containerClass = $isMe ? 'justify-content-end' : 'justify-content-start';
                                            $bubbleClass = $isMe ? 'bg-primary text-white' : ($isStaff ? 'bg-white border-primary border-2 text-dark' : 'bg-white border text-dark');
                                            $metaClass = $isMe ? 'text-end' : 'text-start';
                                            
                                            // Initials
                                            $parts = explode(' ', $reply['replier_name']);
                                            $initials = strtoupper(substr($parts[0], 0, 1));
                                            if (isset($parts[1])) $initials .= strtoupper(substr($parts[1], 0, 1));
                                            
                                            // Sender Label
                                            $senderLabel = $isMe ? 'You' : $reply['replier_name'];
                                            if ($isStaff && !$isMe) $senderLabel .= ' <span class="badge bg-primary bg-opacity-10 text-primary ms-1" style="font-size: 0.65rem;">SUPPORT</span>';
                                        ?>
                                        
                                        <div class="d-flex mb-4 <?php echo $containerClass; ?>">
                                            <?php if (!$isMe): ?>
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar-circle <?php echo $isStaff ? 'bg-primary text-white' : 'bg-secondary bg-opacity-10 text-secondary'; ?> fw-bold rounded-circle d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;" title="<?php echo htmlspecialchars($reply['replier_name']); ?>">
                                                        <?php echo $isStaff ? '<i class="fas fa-headset"></i>' : $initials; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div style="max-width: 75%;">
                                                <div class="chat-bubble p-3 rounded-3 shadow-sm <?php echo $bubbleClass; ?>" style="position: relative;">
                                                    <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                                                </div>
                                                <div class="mt-1 small text-muted <?php echo $metaClass; ?>">
                                                    <span class="fw-bold"><?php echo $senderLabel; ?></span>
                                                    <span class="mx-1">•</span>
                                                    <span><?php echo date('M d, h:i A', strtotime($reply['created_at'])); ?></span>
                                                </div>
                                            </div>

                                            <?php if ($isMe): ?>
                                                <div class="flex-shrink-0 ms-3">
                                                    <div class="avatar-circle bg-light border text-muted fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="You">
                                                        <?php echo $initials; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <div id="scrollAnchor"></div>
                            </div>
                            
                            <?php if ($ticket['status'] !== 'Closed'): ?>
                                <div class="card-footer bg-white p-4 border-top">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-dark small text-uppercase">Post a Reply</label>
                                            <textarea name="reply_message" class="form-control bg-light border-0" rows="4" placeholder="Type your message here..." required style="resize: none;"></textarea>
                                        </div>
                                        <div class="d-flex justify-content-end align-items-center">
                                            <button type="submit" class="btn btn-primary px-4 shadow-sm hover-lift">
                                                <i class="fas fa-paper-plane me-2"></i> Send Reply
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="card-footer bg-light p-4 text-center border-top">
                                    <div class="text-muted mb-2"><i class="fas fa-lock fa-lg"></i></div>
                                    <p class="mb-0 text-muted small">This ticket is closed. Please reopen it if you need further assistance.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Sidebar Column -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-dark mb-3 text-uppercase small ls-1">Ticket Info</h6>
                                
                                <div class="mb-4">
                                    <label class="d-block text-muted small mb-1">Created</label>
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-calendar-alt text-muted me-2"></i>
                                        <span class="fw-medium text-dark"><?php echo date('F d, Y', strtotime($ticket['created_at'])); ?></span>
                                    </div>
                                    <small class="text-muted ms-4"><?php echo date('h:i A', strtotime($ticket['created_at'])); ?></small>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="d-block text-muted small mb-1">Category</label>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-light text-dark border fw-normal px-2 py-1">
                                            <i class="fas fa-tag me-1 text-muted small"></i>
                                            <?php echo htmlspecialchars($ticket['category_name']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mb-0">
                                    <label class="d-block text-muted small mb-1">Priority</label>
                                    <?php
                                    $pClass = match($ticket['priority']) {
                                        'Low' => 'bg-info bg-opacity-10 text-info',
                                        'Medium' => 'bg-warning bg-opacity-10 text-warning',
                                        'High' => 'bg-danger bg-opacity-10 text-danger',
                                        'Critical' => 'bg-danger text-white',
                                        default => 'bg-secondary bg-opacity-10 text-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $pClass; ?> rounded-pill px-3 py-1 fw-medium">
                                        <?php echo htmlspecialchars($ticket['priority']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Help Card -->
                        <div class="card border-0 shadow-sm bg-primary text-white overflow-hidden" style="background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);">
                            <div class="card-body p-4 position-relative">
                                <i class="fas fa-life-ring position-absolute" style="font-size: 80px; opacity: 0.1; right: -10px; bottom: -10px;"></i>
                                <h6 class="fw-bold mb-2">Need immediate help?</h6>
                                <p class="small opacity-90 mb-3">Our support team is available during business hours to assist you.</p>
                                <a href="create.php" class="btn btn-sm btn-light text-primary fw-bold shadow-sm">Open New Ticket</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto-scroll
        document.addEventListener("DOMContentLoaded", function() {
            const conversationBody = document.getElementById('conversationBody');
            const scrollAnchor = document.getElementById('scrollAnchor');
            if (conversationBody && scrollAnchor) {
                scrollAnchor.scrollIntoView({ behavior: 'auto' });
            }
        });
    </script>
</body>
</html>
