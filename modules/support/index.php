<?php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/SupportManager.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getCurrentUser();

$supportManager = new SupportManager();

// Determine if user can see all tickets (Super Admin only) or just their company's
$isSuperAdmin = $auth->hasRole('Super Admin');
$isCompanyAdmin = $auth->hasRole('Admin');
$canManageTickets = $isSuperAdmin || $isCompanyAdmin;

$statusFilter = $_GET['status'] ?? null;
$userId = null;
$companyId = null;

if (!$isSuperAdmin) {
    $companyId = $user['company_id'];
    if (!$isCompanyAdmin) {
        $userId = $user['id'];
    }
}

$tickets = $supportManager->getTickets($userId, $statusFilter, $companyId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .ticket-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            background: white;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .filter-btn:hover, .filter-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .priority-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .priority-Low { background-color: #10b981; }
        .priority-Medium { background-color: #f59e0b; }
        .priority-High { background-color: #ef4444; }
        .priority-Critical { background-color: #7f1d1d; }

        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-Open { background-color: #d1fae5; color: #065f46; }
        .status-In-Progress { background-color: #dbeafe; color: #1e40af; }
        .status-Awaiting-Reply { background-color: #ffedd5; color: #9a3412; }
        .status-Resolved { background-color: #f3f4f6; color: #374151; }
        .status-Closed { background-color: #f3f4f6; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include INCLUDES_PATH . '/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include INCLUDES_PATH . '/header.php'; ?>
            
            <div class="content-area">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Support Tickets</h1>
                        <p class="text-secondary">Track and manage your specific support requests</p>
                    </div>
                    <a href="create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Ticket
                    </a>
                </div>

                <div class="ticket-filters">
                    <a href="index.php" class="filter-btn <?php echo !$statusFilter ? 'active' : ''; ?>">All</a>
                    <a href="index.php?status=Open" class="filter-btn <?php echo $statusFilter === 'Open' ? 'active' : ''; ?>">Open</a>
                    <a href="index.php?status=In Progress" class="filter-btn <?php echo $statusFilter === 'In Progress' ? 'active' : ''; ?>">In Progress</a>
                    <a href="index.php?status=Resolved" class="filter-btn <?php echo $statusFilter === 'Resolved' ? 'active' : ''; ?>">Resolved</a>
                    <a href="index.php?status=Closed" class="filter-btn <?php echo $statusFilter === 'Closed' ? 'active' : ''; ?>">Closed</a>
                </div>

                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Subject</th>
                                    <th>Category</th>
                                    <?php if ($canManageTickets): ?>
                                        <th>User</th>
                                    <?php endif; ?>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tickets)): ?>
                                    <tr>
                                        <td colspan="<?php echo $canManageTickets ? 8 : 7; ?>" style="text-align: center; padding: 40px;">
                                            <div style="color: var(--text-secondary); margin-bottom: 10px;">
                                                <i class="fas fa-ticket-alt" style="font-size: 48px; opacity: 0.5;"></i>
                                            </div>
                                            <p>No tickets found.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($tickets as $ticket): ?>
                                        <tr>
                                            <td>
                                                <a href="view.php?id=<?php echo $ticket['id']; ?>" style="font-weight: 600; color: var(--primary-color);">
                                                    #<?php echo htmlspecialchars($ticket['ticket_number']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div style="font-weight: 500;"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($ticket['category_name']); ?></td>
                                            <?php if ($canManageTickets): ?>
                                                <td><?php echo htmlspecialchars($ticket['creator_name']); ?></td>
                                            <?php endif; ?>
                                            <td>
                                                <span class="priority-dot priority-<?php echo str_replace(' ', '-', $ticket['priority']); ?>"></span>
                                                <?php echo htmlspecialchars($ticket['priority']); ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo str_replace(' ', '-', $ticket['status']); ?>">
                                                    <?php echo htmlspecialchars($ticket['status']); ?>
                                                </span>
                                            </td>
                                            <td style="font-size: 0.9em; color: var(--text-secondary);">
                                                <?php echo date('M d, H:i', strtotime($ticket['updated_at'])); ?>
                                            </td>
                                            <td>
                                                <a href="view.php?id=<?php echo $ticket['id']; ?>" class="btn-icon" title="View">
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
            </div>
        </main>
    </div>
</body>
</html>
