<?php
$pageTitle = 'Inquiries';
$currentPage = 'inquiries';
require_once '../../config/config.php';
require_once '../../includes/admin_layout.php';
require_once '../../classes/Database.php';
require_once '../../classes/Mail.php';

$auth = new Auth();
$auth->requireLogin();

if (!$auth->hasPermission('super_admin', 'view')) {
    header('Location: ' . MODULES_URL . '/dashboard/index.php');
    exit;
}

$db = Database::getInstance();

// Handle Reply POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_id'])) {
    $id = $_POST['reply_id'];
    $replyMessage = trim($_POST['message']);
    
    if ($id && $replyMessage) {
        $inquiry = $db->fetchOne("SELECT * FROM contact_requests WHERE id = ?", [$id]);
        if ($inquiry) {
            $mail = new Mail();
            $subject = "Re: " . $inquiry['subject'];
            $body = "
                <p>Hello " . htmlspecialchars($inquiry['name']) . ",</p>
                <p>Thank you for contacting us. Here is our response to your inquiry:</p>
                <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #3b82f6; margin: 20px 0;'>
                    " . nl2br(htmlspecialchars($replyMessage)) . "
                </div>
                <p>---</p>
                <p><strong>Original Message:</strong><br>" . nl2br(htmlspecialchars($inquiry['message'])) . "</p>
                <br>
                <p>Best Regards,<br>" . APP_NAME . " Team</p>
            ";
            
            if ($mail->sendWithResend($inquiry['email'], $subject, $body)) {
                $db->query("UPDATE contact_requests SET status = 'Replied', replied_at = NOW() WHERE id = ?", [$id]);
                $successMsg = "Reply sent successfully.";
            } else {
                $errorMsg = "Failed to send email.";
            }
        }
    }
}

// Calculate Stats
$stats = $db->fetchOne("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'New' THEN 1 ELSE 0 END) as new_count,
        SUM(CASE WHEN status = 'Replied' THEN 1 ELSE 0 END) as replied_count
    FROM contact_requests
");

// Filter Logic
$statusFilter = $_GET['status'] ?? null;
$query = "SELECT * FROM contact_requests";
$params = [];
if ($statusFilter) {
    // Handle 'New' status specifically if user clicks it, otherwise show all if invalid
    if (in_array($statusFilter, ['New', 'Replied'])) {
        $query .= " WHERE status = ?";
        $params[] = $statusFilter;
    }
}
$query .= " ORDER BY created_at DESC";
$inquiries = $db->fetchAll($query, $params);
?>

<div class="container-fluid px-0">
    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stats-card p-3 rounded bg-white shadow-sm border h-100 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Total Inquiries</div>
                    <div class="h3 mb-0 fw-bold text-dark"><?php echo $stats['total']; ?></div>
                </div>
                <div class="icon-box bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-inbox fa-lg"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card p-3 rounded bg-white shadow-sm border h-100 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">New</div>
                    <div class="h3 mb-0 fw-bold text-danger"><?php echo $stats['new_count']; ?></div>
                </div>
                <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-envelope fa-lg"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card p-3 rounded bg-white shadow-sm border h-100 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Replied</div>
                    <div class="h3 mb-0 fw-bold text-success"><?php echo $stats['replied_count']; ?></div>
                </div>
                <div class="icon-box bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-reply-all fa-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($successMsg)): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?php echo $successMsg; ?></div>
    <?php endif; ?>
    <?php if (isset($errorMsg)): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMsg; ?></div>
    <?php endif; ?>

    <!-- Main Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>Inquiries List</h5>
                <div class="vr mx-2"></div>
                <!-- Filter Pills -->
                <nav class="nav nav-pills nav-sm">
                    <a class="nav-link <?php echo !$statusFilter ? 'active' : ''; ?> px-3 py-1 small fw-medium" href="inquiries.php">All</a>
                    <a class="nav-link <?php echo $statusFilter === 'New' ? 'active' : ''; ?> px-3 py-1 small fw-medium" href="inquiries.php?status=New">New</a>
                    <a class="nav-link <?php echo $statusFilter === 'Replied' ? 'active' : ''; ?> px-3 py-1 small fw-medium" href="inquiries.php?status=Replied">Replied</a>
                </nav>
            </div>
            
            <!-- Search Box -->
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 280px;">
                    <span class="input-group-text bg-light border-end-0 text-muted ps-3"><i class="fas fa-search"></i></span>
                    <input type="text" id="inquirySearch" class="form-control bg-light border-start-0 py-2" placeholder="Search by Name, Email, or Subject..." onkeyup="filterTable()">
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="inquiriesTable">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th scope="col" class="ps-4" style="width: 25%;">User Details</th>
                        <th scope="col" style="width: 35%;">Subject</th>
                        <th scope="col" style="width: 15%;">Status</th>
                        <th scope="col" class="text-end pe-4" style="width: 15%;">Received</th>
                        <th scope="col" class="text-end pe-4" style="width: 10%;">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($inquiries)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted mb-3"><i class="fas fa-inbox fa-3x opacity-25"></i></div>
                                <h6 class="fw-bold text-muted">No inquiries found</h6>
                                <p class="text-muted small mb-0">Try adjusting your filters.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inquiries as $row): ?>
                            <tr style="transition: all 0.2s;">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <?php 
                                            // Initials logic
                                            $parts = explode(' ', $row['name']);
                                            $initials = strtoupper(substr($parts[0], 0, 1));
                                            if (isset($parts[1])) $initials .= strtoupper(substr($parts[1], 0, 1));
                                        ?>
                                        <div class="avatar-circle flex-shrink-0 bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                            <?php echo $initials; ?>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium text-dark text-truncate" style="max-width: 180px;"><?php echo htmlspecialchars($row['name']); ?></span>
                                            <span class="small text-muted text-truncate" style="max-width: 180px;"><?php echo htmlspecialchars($row['email']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($row['subject']); ?>">
                                            <?php echo htmlspecialchars($row['subject']); ?>
                                        </span>
                                        <span class="small text-muted text-truncate" style="max-width: 300px;">
                                            <?php echo htmlspecialchars(substr($row['message'], 0, 80)) . (strlen($row['message']) > 80 ? '...' : ''); ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $sClass = match($row['status']) {
                                        'New' => 'danger',
                                        'Replied' => 'success',
                                        default => 'light'
                                    };
                                    
                                    $icon = match($row['status']) {
                                        'New' => 'fa-envelope',
                                        'Replied' => 'fa-reply',
                                        default => 'fa-circle'
                                    };
                                    ?>
                                    <div class="d-flex align-items-center">
                                        <span class="dot-indicator me-2 rounded-circle <?php echo 'bg-' . $sClass; ?>" style="width: 8px; height: 8px;"></span>
                                        <span class="small fw-medium text-dark"><?php echo htmlspecialchars($row['status']); ?></span>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="d-block text-dark fw-medium" style="font-size: 0.85rem;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
                                    <span class="d-block text-muted small" style="font-size: 0.75rem;"><?php echo date('h:i A', strtotime($row['created_at'])); ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="inquiry_view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border text-primary hover-shadow" data-bs-toggle="tooltip" title="View & Reply">
                                        <i class="fas fa-reply"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterTable() {
    const input = document.getElementById("inquirySearch");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("inquiriesTable");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) { // Start from 1 to skip header
        let visible = false;
        const tds = tr[i].getElementsByTagName("td");
        
        if (tds.length > 1) {
            // Check Name/Email (index 0) and Subject (index 1)
            const userDetails = tds[0].innerText.toLowerCase();
            const subject = tds[1].innerText.toLowerCase();
            
            if (userDetails.includes(filter) || subject.includes(filter)) {
                visible = true;
            }
        }
        
        tr[i].style.display = visible ? "" : "none";
    }
}
</script>

<style>
/* Custom Styles for this page matching tickets.php */
.hover-primary:hover { color: var(--primary-color) !important; text-decoration: underline !important; }
.hover-shadow:hover { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.nav-pills .nav-link { 
    color: var(--text-secondary); 
    border-radius: 20px;
}
.nav-pills .nav-link.active { 
    background-color: var(--primary-color); 
    color: white; 
}
.nav-pills .nav-link:hover:not(.active) {
    background-color: var(--light-bg);
}
</style>

<?php
// Close content wrapper
echo '</div></main></div></body></html>';
?>
