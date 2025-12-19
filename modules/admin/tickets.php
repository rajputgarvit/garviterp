<?php
$pageTitle = 'Ticket Management';
$currentPage = 'tickets';
require_once '../../config/config.php';
require_once '../../includes/admin_layout.php'; // Defines $auth, checks login/permissions
require_once '../../classes/SupportManager.php';

$supportManager = new SupportManager();

// Calculate Stats (Admin View: All Tickets)
$db = Database::getInstance();
$stats = $db->fetchOne("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open_count,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_count,
        SUM(CASE WHEN status IN ('Resolved', 'Closed') THEN 1 ELSE 0 END) as resolved_count
    FROM support_tickets
");

// Filter Logic
$statusFilter = $_GET['status'] ?? null;
// Admin gets all tickets filtered by status if provided
$tickets = $supportManager->getTickets(null, $statusFilter);
?>

<div class="container-fluid px-0">
    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stats-card p-3 rounded bg-white shadow-sm border h-100 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Total Tickets</div>
                    <div class="h3 mb-0 fw-bold text-dark"><?php echo $stats['total']; ?></div>
                </div>
                <div class="icon-box bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-ticket-alt fa-lg"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card p-3 rounded bg-white shadow-sm border h-100 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Open</div>
                    <div class="h3 mb-0 fw-bold text-success"><?php echo $stats['open_count']; ?></div>
                </div>
                <div class="icon-box bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-envelope-open fa-lg"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card p-3 rounded bg-white shadow-sm border h-100 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">In Progress</div>
                    <div class="h3 mb-0 fw-bold text-warning"><?php echo $stats['in_progress_count']; ?></div>
                </div>
                <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-spinner fa-lg"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card p-3 rounded bg-white shadow-sm border h-100 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1">Resolved</div>
                    <div class="h3 mb-0 fw-bold text-secondary"><?php echo $stats['resolved_count']; ?></div>
                </div>
                <div class="icon-box bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-check-circle fa-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>Tickets List</h5>
                <div class="vr mx-2"></div>
                <!-- Filter Pills -->
                <nav class="nav nav-pills nav-sm">
                    <a class="nav-link <?php echo !$statusFilter ? 'active' : ''; ?> px-3 py-1 small fw-medium" href="tickets.php">All</a>
                    <a class="nav-link <?php echo $statusFilter === 'Open' ? 'active' : ''; ?> px-3 py-1 small fw-medium" href="tickets.php?status=Open">Open</a>
                    <a class="nav-link <?php echo $statusFilter === 'In Progress' ? 'active' : ''; ?> px-3 py-1 small fw-medium" href="tickets.php?status=In Progress">In Progress</a>
                    <a class="nav-link <?php echo $statusFilter === 'Resolved' ? 'active' : ''; ?> px-3 py-1 small fw-medium" href="tickets.php?status=Resolved">Resolved</a>
                </nav>
            </div>
            
            <!-- Search Box -->
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 280px;">
                    <span class="input-group-text bg-light border-end-0 text-muted ps-3"><i class="fas fa-search"></i></span>
                    <input type="text" id="ticketSearch" class="form-control bg-light border-start-0 py-2" placeholder="Search by ID, Subject, or User..." onkeyup="filterTable()">
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="ticketsTable">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th scope="col" class="ps-4" style="width: 30%;">Ticket Details</th>
                        <th scope="col" style="width: 20%;">User</th>
                        <th scope="col" style="width: 15%;">Category</th>
                        <th scope="col" style="width: 10%;">Priority</th>
                        <th scope="col" style="width: 10%;">Status</th>
                        <th scope="col" class="text-end pe-4" style="width: 15%;">Last Updated</th>
                        <th scope="col" class="text-end pe-4" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted mb-3"><i class="fas fa-inbox fa-3x opacity-25"></i></div>
                                <h6 class="fw-bold text-muted">No tickets found</h6>
                                <p class="text-muted small mb-0">Try adjusting your filters or search criteria.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr style="transition: all 0.2s;">
                                <td class="ps-4">
                                    <div class="d-flex flex-column">
                                        <a href="ticket_view.php?id=<?php echo $ticket['ticket_number']; ?>" class="fw-bold text-dark text-decoration-none hover-primary mb-1 text-truncate" style="max-width: 300px;">
                                            <?php echo htmlspecialchars($ticket['subject']); ?>
                                        </a>
                                        <span class="small text-muted font-monospace bg-light border rounded px-1 d-inline-block" style="width: fit-content;">
                                            #<?php echo htmlspecialchars($ticket['ticket_number']); ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php 
                                            // Initials logic
                                            $parts = explode(' ', $ticket['creator_name']);
                                            $initials = strtoupper(substr($parts[0], 0, 1));
                                            if (isset($parts[1])) $initials .= strtoupper(substr($parts[1], 0, 1));
                                        ?>
                                        <div class="avatar-circle flex-shrink-0 bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                            <?php echo $initials; ?>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium text-dark text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($ticket['creator_name']); ?></span>
                                            <!-- Optional: Add user email if available later -->
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-normal px-2 py-1">
                                        <i class="fas fa-tag me-1 text-muted small"></i>
                                        <?php echo htmlspecialchars($ticket['category_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $pClass = match($ticket['priority']) {
                                        'Low' => 'bg-info bg-opacity-10 text-info',
                                        'Medium' => 'bg-warning bg-opacity-10 text-warning',
                                        'High' => 'bg-danger bg-opacity-10 text-danger',
                                        'Critical' => 'bg-danger text-white',
                                        default => 'bg-secondary bg-opacity-10 text-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $pClass; ?> rounded-pill px-3 py-1 fw-medium" style="font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($ticket['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $sClass = match($ticket['status']) {
                                        'Open' => 'success',
                                        'In Progress' => 'primary',
                                        'Awaiting Reply' => 'warning',
                                        'Resolved', 'Closed' => 'secondary',
                                        default => 'light'
                                    };
                                    
                                    // Using standard bootstrap text classes for soft badges
                                    $textClass = ($sClass === 'light') ? 'text-dark' : 'text-' . $sClass;
                                    $bgClass = 'bg-' . $sClass . ' bg-opacity-10';
                                    if ($sClass === 'light') $bgClass = 'bg-light';
                                    
                                    $icon = match($ticket['status']) {
                                        'Open' => 'fa-envelope-open',
                                        'In Progress' => 'fa-spinner fa-spin-hover',
                                        'Resolved', 'Closed' => 'fa-check',
                                        default => 'fa-circle'
                                    };
                                    ?>
                                    <div class="d-flex align-items-center">
                                        <span class="dot-indicator me-2 rounded-circle <?php echo 'bg-' . $sClass; ?>" style="width: 8px; height: 8px;"></span>
                                        <span class="small fw-medium text-dark"><?php echo htmlspecialchars($ticket['status']); ?></span>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="d-block text-dark fw-medium" style="font-size: 0.85rem;"><?php echo date('M d, Y', strtotime($ticket['updated_at'])); ?></span>
                                    <span class="d-block text-muted small" style="font-size: 0.75rem;"><?php echo date('h:i A', strtotime($ticket['updated_at'])); ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="ticket_view.php?id=<?php echo $ticket['ticket_number']; ?>" class="btn btn-sm btn-light border text-primary hover-shadow" data-bs-toggle="tooltip" title="View Ticket">
                                        <i class="fas fa-external-link-alt"></i>
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
    const input = document.getElementById("ticketSearch");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("ticketsTable");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) { // Start from 1 to skip header
        let visible = false;
        const tds = tr[i].getElementsByTagName("td");
        
        if (tds.length > 1) {
            // Check Subject (index 0) and User (index 1)
            // Subject is inside the first 'a' tag in the first separate div
            const subject = tds[0].innerText.toLowerCase();
            const user = tds[1].innerText.toLowerCase();
            
            if (subject.includes(filter) || user.includes(filter)) {
                visible = true;
            }
        }
        
        tr[i].style.display = visible ? "" : "none";
    }
}
</script>

<style>
/* Custom Styles for this page */
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
.fa-spin-hover:hover {
    animation: fa-spin 2s infinite linear;
}
</style>

</div> <!-- End content-area (opened in admin_layout) -->
</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>
