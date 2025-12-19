<?php
$pageTitle = 'View Inquiry';
$currentPage = 'inquiries';
require_once '../../config/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Mail.php';
require_once '../../classes/Auth.php';

// Auth Check & Setup
$auth = new Auth();
$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

if (!$auth->hasPermission('super_admin', 'view')) {
    header('Location: ' . MODULES_URL . '/dashboard/index.php');
    exit;
}

$db = Database::getInstance();
$requestId = $_GET['id'] ?? 0;
$request = $db->fetchOne("SELECT * FROM contact_requests WHERE id = ?", [$requestId]);

if (!$request) {
    header('Location: inquiries.php');
    exit;
}

// Handle Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $replyMessage = trim($_POST['reply_message']);
    
    if ($replyMessage) {
        $mail = new Mail();
        $subject = "Re: " . $request['subject'];
        $body = "
            <p>Hello " . htmlspecialchars($request['name']) . ",</p>
            <p>Thank you for contacting us. Here is our response to your inquiry:</p>
            <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #3b82f6; margin: 20px 0;'>
                " . nl2br(htmlspecialchars($replyMessage)) . "
            </div>
            <p>---</p>
            <p><strong>Original Message:</strong><br>" . nl2br(htmlspecialchars($request['message'])) . "</p>
            <br>
            <p>Best Regards,<br>" . APP_NAME . " Team</p>
        ";
        
        // Attempt to send email
        if ($mail->sendWithResend($request['email'], $subject, $body, 'Acculynce Enquiries', 'no-reply@acculynce.com')) {
            // Save reply to DB
            $db->query(
                "INSERT INTO contact_replies (request_id, user_id, message) VALUES (?, ?, ?)",
                [$requestId, $currentUser['id'], $replyMessage]
            );
            
            // Update request status
            $db->query("UPDATE contact_requests SET status = 'Replied', replied_at = NOW() WHERE id = ?", [$requestId]);
            
            // Redirect to refresh and show message
            header("Location: inquiry_view.php?id=$requestId&msg=replied");
            exit;
        } else {
            $error = "Failed to send email. Please check configuration.";
        }
    }
}

// Fetch Replies
$replies = $db->fetchAll(
    "SELECT r.*, u.full_name, u.email as admin_email 
     FROM contact_replies r 
     LEFT JOIN users u ON r.user_id = u.id 
     WHERE r.request_id = ? 
     ORDER BY r.created_at ASC", 
    [$requestId]
);

// Include Layout AFTER logic/redirects
require_once '../../includes/admin_layout.php';
?>

<div class="container-fluid px-0 h-100 d-flex flex-column" style="height: calc(100vh - 100px) !important;">
    
    <!-- Header -->
    <div class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center mb-3 shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <a href="inquiries.php" class="btn btn-light btn-sm border text-muted"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    <?php echo htmlspecialchars($request['subject']); ?>
                </h5>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="small text-muted">From: <strong><?php echo htmlspecialchars($request['name']); ?></strong> &lt;<?php echo htmlspecialchars($request['email']); ?>&gt;</span>
                    <span class="text-muted small">&bull;</span>
                    <span class="small text-muted"><?php echo date('M d, Y h:i A', strtotime($request['created_at'])); ?></span>
                </div>
            </div>
        </div>
        <div>
            <?php if ($request['status'] === 'New'): ?>
                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">New Inquiry</span>
            <?php else: ?>
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Replied</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'replied'): ?>
        <div class="alert alert-success mx-4"><i class="fas fa-check-circle me-2"></i>Reply sent successfully.</div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger mx-4"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Chat Area -->
    <div class="flex-grow-1 overflow-auto px-4 pb-4" id="chatContainer">
        
        <!-- Original Message (Left) -->
        <div class="d-flex mb-4 justify-content-start">
            <div class="d-flex flex-column align-items-start" style="max-width: 75%;">
                <div class="d-flex align-items-center mb-1 gap-2">
                    <div class="avatar-circle bg-secondary bg-opacity-10 text-secondary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.75rem;">
                        <?php echo strtoupper(substr($request['name'], 0, 1)); ?>
                    </div>
                    <span class="small fw-bold text-dark"><?php echo htmlspecialchars($request['name']); ?></span>
                    <span class="small text-muted"><?php echo date('M d, H:i', strtotime($request['created_at'])); ?></span>
                </div>
                <div class="chat-bubble bg-white border border-2 text-dark p-3 rounded-3 shadow-sm position-relative">
                    <p class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($request['message']); ?></p>
                </div>
            </div>
        </div>

        <!-- Replies (Right - Admin) -->
        <?php foreach ($replies as $reply): ?>
            <div class="d-flex mb-4 justify-content-end">
                <div class="d-flex flex-column align-items-end" style="max-width: 75%;">
                    <div class="d-flex align-items-center mb-1 gap-2">
                        <span class="small text-muted text-end">
                            <?php echo date('M d, H:i', strtotime($reply['created_at'])); ?>
                            <i class="fas fa-check-double ms-1 text-primary"></i>
                        </span>
                        <span class="small fw-bold text-dark"><?php echo htmlspecialchars($reply['full_name']); ?></span>
                        <div class="avatar-circle bg-primary text-white fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.75rem;">
                            <?php echo strtoupper(substr($reply['full_name'], 0, 1)); ?>
                        </div>
                    </div>
                    <div class="chat-bubble bg-primary text-white p-3 rounded-3 shadow-sm position-relative">
                        <p class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($reply['message']); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- Reply Input Area -->
    <div class="bg-light border-top p-4 mt-auto">
        <form method="POST" action="">
            <div class="d-flex gap-3 align-items-start">
                <div class="flex-grow-1 position-relative">
                    <textarea name="reply_message" class="form-control border-0 shadow-sm p-3" rows="3" placeholder="Type your reply here... (Enter to send, Shift+Enter for new line)" style="resize: none;" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 py-3 px-4 shadow-sm" style="height: 100%;">
                    <i class="fas fa-paper-plane"></i>
                    <span class="d-none d-md-inline">Send</span>
                </button>
            </div>
            <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i>Reply will be sent to <strong><?php echo htmlspecialchars($request['email']); ?></strong></div>
        </form>
    </div>

</div>

<script>
// Auto scroll to bottom
const chatContainer = document.getElementById('chatContainer');
chatContainer.scrollTop = chatContainer.scrollHeight;

// Optional: submit on Enter
document.querySelector('textarea[name="reply_message"]').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (this.value.trim() !== '') {
            this.form.submit();
        }
    }
});
</script>

<style>
/* Chat Bubble Styles */
.chat-bubble {
    font-size: 0.95rem;
    line-height: 1.5;
}
/* Left bubble arrow */
.justify-content-start .chat-bubble {
    border-top-left-radius: 0 !important;
}
/* Right bubble arrow */
.justify-content-end .chat-bubble {
    border-top-right-radius: 0 !important;
    background: linear-gradient(135deg, var(--primary-color), #4f46e5); /* Gradient for admin */
}
</style>

<?php
// Close content wrapper
echo '</div></main></div></body></html>';
?>
