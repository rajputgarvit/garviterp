<?php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/SupportManager.php';
require_once '../../classes/Database.php'; // Ensure DB is loaded

$auth = new Auth();
$auth->requireLogin();

// Ensure Super Admin access (replicating check from admin_layout to be safe before logic runs)
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

// Handle POST actions (Reply / Update Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reply_message'])) {
        $message = trim($_POST['reply_message']);
        if (!empty($message)) {
            $supportManager->addReply($ticketId, $currentUser['id'], $message);
            header("Location: ticket_view.php?id=$ticketId&msg=replied");
            exit;
        }
    }
    
    if (isset($_POST['update_status'])) {
        $newStatus = $_POST['status'];
        $supportManager->updateStatus($ticketId, $newStatus);
        header("Location: ticket_view.php?id=$ticketId&msg=status_updated");
        exit;
    }
}

// Setup for Layout
$pageTitle = 'View Ticket';
$currentPage = 'tickets';
require_once '../../includes/admin_layout.php';

$ticket = $supportManager->getTicketDetails($ticketId);
if (!$ticket) {
    echo "<div class='alert alert-danger'>Ticket not found.</div>";
    exit;
}
?>

<div class="row">
    <!-- Conversation Column -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 text-primary">#<?php echo htmlspecialchars($ticket['ticket_number']); ?> - <?php echo htmlspecialchars($ticket['subject']); ?></h5>
                    <div class="text-muted small">
                        Created by <strong><?php echo htmlspecialchars($ticket['creator_name']); ?></strong> 
                        on <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?>
                    </div>
                </div>
                <a href="tickets.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
            <div class="card-body" style="background: #f9f9f9; max-height: 600px; overflow-y: auto;">
                <?php foreach ($ticket['replies'] as $reply): ?>
                    <?php 
                        $roles = $reply['replier_roles'] ?? '';
                        $isStaff = (strpos($roles, 'Super Admin') !== false || strpos($roles, 'Admin') !== false); 
                        $align = $isStaff ? 'ms-auto' : 'me-auto';
                        $bg = $isStaff ? 'bg-white border-primary border' : 'bg-white border';
                        $maxWidth = '85%';
                    ?>
                    <div class="d-flex mb-3 <?php echo $align; ?>" style="max-width: <?php echo $maxWidth; ?>; width: fit-content;">
                        <div class="p-3 rounded shadow-sm <?php echo $bg; ?>">
                            <div class="d-flex justify-content-between align-items-center mb-2 gap-3">
                                <strong class="<?php echo $isStaff ? 'text-primary' : 'text-dark'; ?>">
                                    <?php echo htmlspecialchars($reply['replier_name']); ?>
                                </strong>
                                <span class="text-muted small" style="font-size: 0.75rem;">
                                    <?php echo date('M d, H:i A', strtotime($reply['created_at'])); ?>
                                </span>
                            </div>
                            <div class="text-dark">
                                <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="card-footer bg-white">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Add Reply</label>
                        <textarea name="reply_message" class="form-control" rows="4" placeholder="Type your reply here..." required></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-light fw-bold">Ticket Details</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted small text-uppercase">Status</label>
                        <select name="status" class="form-select">
                            <?php 
                            $statuses = ['Open', 'In Progress', 'Awaiting Reply', 'Resolved', 'Closed'];
                            foreach ($statuses as $st): 
                            ?>
                                <option value="<?php echo $st; ?>" <?php echo $ticket['status'] === $st ? 'selected' : ''; ?>>
                                    <?php echo $st; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-sm btn-outline-primary w-100 mb-3">Update Status</button>
                </form>

                <hr>

                <div class="mb-3">
                    <label class="d-block text-muted small text-uppercase">Priority</label>
                    <span class="badge bg-<?php echo $ticket['priority'] === 'High' || $ticket['priority'] === 'Critical' ? 'danger' : ($ticket['priority'] === 'Medium' ? 'warning' : 'success'); ?> fs-6">
                        <?php echo htmlspecialchars($ticket['priority']); ?>
                    </span>
                </div>

                <div class="mb-3">
                    <label class="d-block text-muted small text-uppercase">Category</label>
                    <span class="fw-bold"><?php echo htmlspecialchars($ticket['category_name']); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

</div> <!-- End content-area -->
</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>
