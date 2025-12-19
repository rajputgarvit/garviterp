<?php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/SupportManager.php';
require_once '../../classes/Database.php';

$auth = new Auth();
$auth->requireLogin();

if (!$auth->hasPermission('super_admin', 'view')) {
   header('Location: ' . MODULES_URL . '/dashboard/index.php');
   exit;
}

$currentUser = $auth->getCurrentUser();
$supportManager = new SupportManager();

$ticketId = $_GET['id'] ?? null;
if (!$ticketId) {
    header("Location: tickets.php");
    exit;
}

// Fetch ticket details early to get the ID and validate existence
$ticket = $supportManager->getTicketDetails($ticketId);
if (!$ticket) {
    echo "<div class='container py-4'><div class='alert alert-danger'>Ticket not found. <a href='tickets.php'>Back to list</a></div></div>";
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reply_message'])) {
        $message = trim($_POST['reply_message']);
        if (!empty($message)) {
            // Use resolved numeric ID for database operations
            $supportManager->addReply($ticket['id'], $currentUser['id'], $message);
            // Use ticket number for URL to keep it clean
            header("Location: ticket_view.php?id=" . $ticket['ticket_number'] . "&msg=replied");
            exit;
        }
    }
    
    if (isset($_POST['update_status'])) {
        $newStatus = $_POST['status'];
        // Use resolved numeric ID for database operations
        $supportManager->updateStatus($ticket['id'], $newStatus);
        header("Location: ticket_view.php?id=" . $ticket['ticket_number'] . "&msg=status_updated");
        exit;
    }
}

// Layout Setup
$pageTitle = 'View Ticket';
$currentPage = 'tickets';
require_once '../../includes/admin_layout.php';
?>

<div class="container-fluid px-0 h-100">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="avatar-box rounded-3 bg-light d-flex align-items-center justify-content-center text-primary" style="width: 50px; height: 50px;">
                            <i class="fas fa-ticket-alt fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                            <span class="badge bg-light text-secondary border font-monospace ms-2">#<?php echo htmlspecialchars($ticket['ticket_number']); ?></span>
                            
                            <?php
                            $sClass = match($ticket['status']) {
                                'Open' => 'success',
                                'In Progress' => 'primary',
                                'Awaiting Reply' => 'warning',
                                'Resolved', 'Closed' => 'secondary',
                                default => 'light'
                            };
                            ?>
                            <span class="badge bg-<?php echo $sClass; ?> bg-opacity-10 text-<?php echo $sClass; ?> ms-1">
                                <?php echo htmlspecialchars($ticket['status']); ?>
                            </span>
                        </div>
                        <div class="text-muted small">
                            Created on <span class="fw-medium text-dark"><?php echo date('F d, Y \a\t h:i A', strtotime($ticket['created_at'])); ?></span>
                            in <span class="fw-medium text-dark"><?php echo htmlspecialchars($ticket['category_name']); ?></span>
                        </div>
                    </div>
                </div>
                <a href="tickets.php" class="btn btn-outline-secondary btn-sm shadow-sm hover-lift">
                    <i class="fas fa-arrow-left me-1"></i> Back to Tickets
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Conversation Area -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-comments me-2 text-primary"></i>Conversation History</h6>
                </div>
                
                <div class="card-body bg-light bg-opacity-50 p-4" style="min-height: 400px; max-height: 600px; overflow-y: auto;" id="conversationBody">
                    <?php if (empty($ticket['replies'])): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-comment-dots fa-3x opacity-25 mb-3"></i>
                            <p>No messages in this conversation yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ticket['replies'] as $reply): ?>
                            <?php 
                                $roles = $reply['replier_roles'] ?? '';
                                $isStaff = (strpos($roles, 'Super Admin') !== false || strpos($roles, 'Admin') !== false); 
                                
                                // Layout variables
                                $containerClass = $isStaff ? 'justify-content-end' : 'justify-content-start';
                                $bubbleClass = $isStaff ? 'bg-primary text-white' : 'bg-white border text-dark';
                                $metaClass = $isStaff ? 'text-end' : 'text-start';
                                
                                // User Initials
                                $parts = explode(' ', $reply['replier_name']);
                                $initials = strtoupper(substr($parts[0], 0, 1));
                                if (isset($parts[1])) $initials .= strtoupper(substr($parts[1], 0, 1));
                            ?>
                            
                            <div class="d-flex mb-4 <?php echo $containerClass; ?>">
                                <?php if (!$isStaff): ?>
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-circle bg-secondary bg-opacity-10 text-secondary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="<?php echo htmlspecialchars($reply['replier_name']); ?>">
                                            <?php echo $initials; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="max-width: 75%;">
                                    <div class="chat-bubble p-3 rounded-3 shadow-sm <?php echo $bubbleClass; ?>" style="position: relative;">
                                        <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                                    </div>
                                    <div class="mt-1 small text-muted <?php echo $metaClass; ?>">
                                        <span class="fw-bold"><?php echo htmlspecialchars($reply['replier_name']); ?></span>
                                        <span class="mx-1">•</span>
                                        <span><?php echo date('M d, h:i A', strtotime($reply['created_at'])); ?></span>
                                    </div>
                                </div>

                                <?php if ($isStaff): ?>
                                    <div class="flex-shrink-0 ms-3">
                                        <div class="avatar-circle bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="<?php echo htmlspecialchars($reply['replier_name']); ?>">
                                            <?php echo $initials; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <!-- Anchor for scrolling to bottom -->
                    <div id="scrollAnchor"></div>
                </div>
                
                <div class="card-footer bg-white p-4 border-top">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small text-uppercase">Reply to Ticket</label>
                            <textarea name="reply_message" class="form-control bg-light border-0" rows="4" placeholder="Type your reply here... (Markdown supported)" required style="resize: none;"></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i> Press <strong>Enter</strong> to send
                            </div>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm hover-lift">
                                <i class="fas fa-paper-plane me-2"></i> Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Ticket Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">Ticket Information</h6>
                </div>
                <div class="card-body p-4">
                    <!-- Status Updater -->
                    <form method="POST" class="mb-4">
                        <label class="form-label text-muted small text-uppercase fw-bold mb-2">Current Status</label>
                        <div class="input-group">
                            <select name="status" class="form-select border-secondary border-opacity-25">
                                <?php 
                                $statuses = ['Open', 'In Progress', 'Awaiting Reply', 'Resolved', 'Closed'];
                                foreach ($statuses as $st): 
                                ?>
                                    <option value="<?php echo $st; ?>" <?php echo $ticket['status'] === $st ? 'selected' : ''; ?>>
                                        <?php echo $st; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-outline-primary">Update</button>
                        </div>
                    </form>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <small class="d-block text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Priority</small>
                                <?php
                                $pClass = match($ticket['priority']) {
                                    'Low' => 'success',
                                    'Medium' => 'warning',
                                    'High' => 'danger',
                                    'Critical' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $pClass; ?> bg-opacity-10 text-<?php echo $pClass; ?> border border-<?php echo $pClass; ?> border-opacity-10">
                                    <?php echo htmlspecialchars($ticket['priority']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <small class="d-block text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Category</small>
                                <div class="fw-medium text-dark text-truncate" title="<?php echo htmlspecialchars($ticket['category_name']); ?>">
                                    <?php echo htmlspecialchars($ticket['category_name']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-secondary border-opacity-10">

                    <!-- Requester Info -->
                    <div>
                        <label class="form-label text-muted small text-uppercase fw-bold mb-3">Requester</label>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-primary text-white fw-bold rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <?php echo strtoupper(substr($ticket['creator_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($ticket['creator_name']); ?></div>
                                <div class="small text-muted">Customer Account</div>
                            </div>
                        </div>
                        <div class="mt-3 d-grid">
                            <a href="users.php?search=<?php echo urlencode($ticket['creator_name']); ?>" class="btn btn-sm btn-light border text-secondary hover-dark">
                                <i class="fas fa-user me-2"></i>View Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions (Optional placeholder for future) -->
            <div class="card border-0 shadow-sm bg-primary text-white overflow-hidden" style="background: linear-gradient(135deg, var(--primary-color) 0%, #2a5298 100%);">
                <div class="card-body p-4 position-relative">
                    <i class="fas fa-headset position-absolute" style="font-size: 100px; opacity: 0.1; right: -20px; bottom: -20px;"></i>
                    <h6 class="fw-bold mb-3">Internal Note</h6>
                    <p class="small opacity-75 mb-3">Need to leave a private note for other admins? Feature coming soon.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-scroll to bottom of conversation
    document.addEventListener("DOMContentLoaded", function() {
        const conversationBody = document.getElementById('conversationBody');
        const scrollAnchor = document.getElementById('scrollAnchor');
        if (conversationBody && scrollAnchor) {
            scrollAnchor.scrollIntoView({ behavior: 'auto' });
        }
    });

    // Hover Lift Effect
    const liftElements = document.querySelectorAll('.hover-lift');
    liftElements.forEach(el => {
        el.addEventListener('mouseenter', () => el.style.transform = 'translateY(-2px)');
        el.addEventListener('mouseleave', () => el.style.transform = 'translateY(0)');
        el.style.transition = 'transform 0.2s';
    });
</script>

<style>
/* Chat Bubbles */
.chat-bubble {
    font-size: 0.95rem;
    line-height: 1.5;
}

/* Scrollbar Styling */
#conversationBody::-webkit-scrollbar {
    width: 6px;
}
#conversationBody::-webkit-scrollbar-track {
    background: transparent;
}
#conversationBody::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.1);
    border-radius: 3px;
}
#conversationBody:hover::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.2);
}

.avatar-circle { font-family: 'Inter', sans-serif; letter-spacing: -0.5px; }

/* Soft Badges */
.bg-success-soft { background-color: #d1e7dd; color: #0f5132; }
.bg-primary-soft { background-color: #cfe2ff; color: #084298; }
.bg-warning-soft { background-color: #fff3cd; color: #664d03; }
.bg-secondary-soft { background-color: #e2e3e5; color: #41464b; }
</style>

</div> <!-- End content-area (opened in admin_layout) -->
</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>
