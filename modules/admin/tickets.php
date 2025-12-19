<?php
$pageTitle = 'Ticket Management';
$currentPage = 'tickets';
require_once '../../config/config.php';
require_once '../../includes/admin_layout.php'; // Defines $auth, checks login/permissions
require_once '../../classes/SupportManager.php';

$supportManager = new SupportManager();

// Admin always sees all tickets
$statusFilter = $_GET['status'] ?? null;
$tickets = $supportManager->getTickets(null, $statusFilter);
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title">All Support Tickets</div>
        <div style="display: flex; gap: 10px;">
            <a href="tickets.php" class="btn btn-sm <?php echo !$statusFilter ? 'btn-primary' : 'btn-light'; ?>">All</a>
            <a href="tickets.php?status=Open" class="btn btn-sm <?php echo $statusFilter === 'Open' ? 'btn-primary' : 'btn-light'; ?>">Open</a>
            <a href="tickets.php?status=In Progress" class="btn btn-sm <?php echo $statusFilter === 'In Progress' ? 'btn-primary' : 'btn-light'; ?>">In Progress</a>
            <a href="tickets.php?status=Resolved" class="btn btn-sm <?php echo $statusFilter === 'Resolved' ? 'btn-primary' : 'btn-light'; ?>">Resolved</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>User</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No tickets found.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td>
                                <a href="ticket_view.php?id=<?php echo $ticket['id']; ?>" class="fw-bold text-primary">
                                    #<?php echo htmlspecialchars($ticket['ticket_number']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['category_name']); ?></td>
                            <td>
                                <div><?php echo htmlspecialchars($ticket['creator_name']); ?></div>
                            </td>
                            <td>
                                <?php
                                $pClass = match($ticket['priority']) {
                                    'Low' => 'success',
                                    'Medium' => 'warning',
                                    'High', 'Critical' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $pClass; ?>"><?php echo htmlspecialchars($ticket['priority']); ?></span>
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
                                ?>
                                <span class="badge bg-<?php echo $sClass; ?>"><?php echo htmlspecialchars($ticket['status']); ?></span>
                            </td>
                            <td class="text-muted small">
                                <?php echo date('M d, H:i', strtotime($ticket['updated_at'])); ?>
                            </td>
                            <td>
                                <a href="ticket_view.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-icon btn-light" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div> <!-- End content-area (opened in admin_layout) -->
</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>
