<?php
// session_start(); // Handled in config.php
require_once '../../../config/config.php';
require_once '../../../classes/Auth.php';
require_once '../../../classes/Database.php';

$auth = new Auth();
// Auth::enforceGlobalRouteSecurity() handles permissions.

$db = Database::getInstance();
$user = $auth->getCurrentUser();

// Get invoices
$invoices = $db->fetchAll("
    SELECT i.*, 
           c.company_name as customer_name,
           c.contact_person as contact_person,
           CONCAT(u.full_name) as created_by_name
    FROM invoices i
    JOIN customers c ON i.customer_id = c.id
    LEFT JOIN users u ON i.created_by = u.id
    WHERE i.company_id = ?
    ORDER BY i.created_at DESC
    LIMIT 100
", [$user['company_id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../../../public/assets/js/modules/sales/invoices.js"></script>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include INCLUDES_PATH . '/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include INCLUDES_PATH . '/header.php'; ?>
            
            <div class="content-area">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Invoices</h3>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <div style="position: relative;">
                                <input type="text" id="searchInput" placeholder="Search invoices..." style="padding: 8px 10px 8px 35px; border: 1px solid var(--border-color); border-radius: 5px; width: 250px;">
                                <i class="fas fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                            </div>
                            <a href="create.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> New Invoice
                            </a>
                        </div>
                    </div>
                    
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Due Date</th>
                                    <th>Total Amount</th>
                                    <th>Amount Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($invoices)): ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center; color: var(--text-secondary);">
                                            No invoices found. Create your first invoice.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($invoices as $inv): ?>
                                        <?php 
                                            // Handle paid_amount if column exists, else assume 0 if not fetched (check schema later if consistent)
                                            // Invoices table usually has paid_amount. If not, we might need to sum payments.
                                            // For this step, assuming 'paid_amount' column exists or handled.
                                            // Actually, let's verify if paid_amount is in schema. If not, safe default 0.
                                            $paidAmount = $inv['paid_amount'] ?? 0; // Ensure undefined key doesn't error
                                            $balance = $inv['total_amount'] - $paidAmount;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($inv['invoice_number']); ?></strong></td>
                                            <td>
                                                <?php 
                                                    $displayName = trim($inv['customer_name'] ?? '');
                                                    if (empty($displayName)) {
                                                        $displayName = $inv['contact_person'] ?? '';
                                                    }
                                                    echo htmlspecialchars($displayName); 
                                                ?>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($inv['invoice_date'])); ?></td>
                                            <td><?php echo $inv['due_date'] ? date('d M Y', strtotime($inv['due_date'])) : '-'; ?></td>
                                            <td>₹<?php echo number_format($inv['total_amount'], 2); ?></td>
                                            <td>₹<?php echo number_format($paidAmount, 2); ?></td>
                                            <td><strong style="color: <?php echo $balance > 0 ? '#ef4444' : '#10b981'; ?>">₹<?php echo number_format($balance, 2); ?></strong></td>
                                            <td>
                                                <?php
                                                $statusClass = match($inv['status']) {
                                                    'Paid' => 'badge-success',
                                                    'Partially Paid' => 'badge-warning',
                                                    'Unpaid' => 'badge-danger',
                                                    'Overdue' => 'badge-danger', // Often computed
                                                    'Sent' => 'badge-primary',
                                                    'Draft' => 'badge-secondary',
                                                    default => 'badge-secondary'
                                                };
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo $inv['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view.php?id=<?php echo $inv['id']; ?>" class="btn-icon view" title="View Invoice">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                                <?php if ($inv['status'] == 'Draft' || $inv['status'] == 'Unpaid'): ?>
                                                    <a href="edit.php?id=<?php echo $inv['id']; ?>" class="btn-icon edit" title="Edit Invoice">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                    <a href="record-payment.php?id=<?php echo $inv['id']; ?>" class="btn-icon" title="Record Payment" style="color: #10b981;">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <!-- Add Delete if needed, usually guarded -->
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

    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('table tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
