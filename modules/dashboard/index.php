<?php
// session_start(); // Handled in config.php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Database.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user = $auth->getCurrentUser();
$companyId = $user['company_id'];

// Check subscription status
require_once '../../classes/Subscription.php';
$subscription = new Subscription();
if (!$subscription->hasActiveSubscription($user['company_id'])) {
    header('Location: ../subscription/subscription-expired.php');
    exit;
}

// Check if company exists for Super Admin, if not redirect to setup
if ($auth->hasRole('Super Admin')) {
    $companyExists = false;
    if (!empty($companyId)) {
        $companyCheck = $db->fetchOne("SELECT id FROM company_settings WHERE id = ?", [$companyId]);
        if ($companyCheck) {
            $companyExists = true;
        }
    }
    
    if (!$companyExists) {
        header('Location: ' . MODULES_URL . '/admin/setup_company.php');
        exit;
    }
}

// -------------------------
// Dashboard Analytics & KPI
// -------------------------

// 1. Key Financial Metrics
$stats = [
    // Revenue (Sum of Paid Invoices in current year)
    'total_revenue' => $db->fetchOne("
        SELECT SUM(total_amount) as total 
        FROM invoices 
        WHERE status = 'Paid' 
        AND YEAR(invoice_date) = YEAR(CURDATE()) 
        AND company_id = ?", [$companyId])['total'] ?? 0,

    // Receivables (Pending Invoices)
    'pending_invoices_amount' => $db->fetchOne("
        SELECT SUM(balance_amount) as total 
        FROM invoices 
        WHERE status NOT IN ('Paid', 'Cancelled', 'Draft') 
        AND company_id = ?", [$companyId])['total'] ?? 0,

    // Payables (Pending Bills)
    'pending_bills_amount' => $db->fetchOne("
        SELECT SUM(balance_amount) as total 
        FROM purchase_invoices 
        WHERE status NOT IN ('Paid', 'Cancelled', 'Draft') 
        AND company_id = ?", [$companyId])['total'] ?? 0,

    // Cash Position
    'cash_balance' => $db->fetchOne("
        SELECT SUM(current_balance) as total 
        FROM bank_accounts 
        WHERE is_active = 1 
        AND company_id = ?", [$companyId])['total'] ?? 0
];

// 2. Sales Trend (Last 6 Months)
$salesTrend = ['labels' => [], 'data' => []];
for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd = date('Y-m-t', strtotime("-$i months"));
    $monthLabel = date('M', strtotime("-$i months"));
    
    $monthlyRevenue = $db->fetchOne("
        SELECT SUM(total_amount) as total 
        FROM invoices 
        WHERE status != 'Cancelled' 
        AND invoice_date BETWEEN ? AND ? 
        AND company_id = ?", 
        [$monthStart, $monthEnd, $companyId]
    )['total'] ?? 0;

    $salesTrend['labels'][] = $monthLabel;
    $salesTrend['data'][] = $monthlyRevenue;
}

// 3. Invoice Status Distribution
$invoiceCounts = $db->fetchOne("
    SELECT 
        SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) as paid,
        SUM(CASE WHEN status IN ('Sent', 'Partially Paid') THEN 1 ELSE 0 END) as sent,
        SUM(CASE WHEN status = 'Overdue' THEN 1 ELSE 0 END) as overdue,
        SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) as draft
    FROM invoices 
    WHERE company_id = ?", [$companyId]);

// 4. Recent Invoices
$recent_invoices = $db->fetchAll("
    SELECT i.id, i.invoice_number, c.company_name, c.contact_person, i.invoice_date, i.total_amount, i.status 
    FROM invoices i 
    JOIN customers c ON i.customer_id = c.id 
    WHERE i.company_id = ? 
    ORDER BY i.created_at DESC 
    LIMIT 10", [$companyId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <?php include INCLUDES_PATH . '/sidebar.php'; ?>
        </aside>
        
        <main class="main-content">
            <?php include INCLUDES_PATH . '/header.php'; ?>
            
            <div class="content-area">
                <!-- Financial Highlights -->
                <div class="stats-grid mb-4">
                    <!-- Total Revenue -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-value">₹<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></div>
                                <div class="stat-label">Total Revenue (This Year)</div>
                            </div>
                            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                    </div>

                    <!-- To Collect -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-value">₹<?php echo number_format($stats['pending_invoices_amount'] ?? 0, 2); ?></div>
                                <div class="stat-label">To Collect (Receivables)</div>
                            </div>
                            <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                        </div>
                    </div>

                    <!-- To Pay -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-value">₹<?php echo number_format($stats['pending_bills_amount'] ?? 0, 2); ?></div>
                                <div class="stat-label">To Pay (Payables)</div>
                            </div>
                            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Cash Balance -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-value">₹<?php echo number_format($stats['cash_balance'] ?? 0, 2); ?></div>
                                <div class="stat-label">Cash & Bank Balance</div>
                            </div>
                            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                                <i class="fas fa-landmark"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row g-4 mb-4">
                    <!-- Sales Trend -->
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Sales & Revenue Trend</h3>
                                <small class="text-muted">Last 6 Months</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="salesTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Invoice Status -->
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Invoice Status</h3>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <div style="width: 100%; max-width: 280px; height: 300px; position: relative;">
                                    <canvas id="invoiceStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Invoices (Full Width) -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Recent Invoices</h3>
                        <a href="<?php echo MODULES_URL; ?>/sales/invoices/index.php" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Invoice Number</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_invoices)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                No invoices found. Create your first invoice to get started.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_invoices as $invoice): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold">
                                                    <a href="<?php echo MODULES_URL; ?>/sales/invoices/view.php?id=<?php echo $invoice['id'] ?? '#'; ?>" class="text-decoration-none text-dark">
                                                        <?php echo htmlspecialchars($invoice['invoice_number']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle sm bg-soft-primary text-primary me-2">
                                                            <?php 
                                                            $displayName = !empty($invoice['company_name']) ? $invoice['company_name'] : $invoice['contact_person'];
                                                            echo strtoupper(substr($displayName, 0, 1)); 
                                                            ?>
                                                        </div>
                                                        <span class="fw-medium"><?php echo htmlspecialchars($displayName); ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-muted"><?php echo date('d M, Y', strtotime($invoice['invoice_date'])); ?></td>
                                                <td class="fw-bold">₹<?php echo number_format($invoice['total_amount'], 2); ?></td>
                                                <td>
                                                    <?php
                                                    $statusColors = [
                                                        'Paid' => 'success',
                                                        'Sent' => 'primary',
                                                        'Partially Paid' => 'info',
                                                        'Overdue' => 'danger',
                                                        'Draft' => 'secondary'
                                                    ];
                                                    $bgClass = $statusColors[$invoice['status']] ?? 'warning';
                                                    ?>
                                                    <span class="badge bg-soft-<?php echo $bgClass; ?> text-<?php echo $bgClass; ?>">
                                                        <?php echo htmlspecialchars($invoice['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data from PHP
        const salesData = <?php echo json_encode($salesTrend); ?>;
        const invoiceStats = <?php echo json_encode($invoiceCounts); ?>;

        // Sales Trend Chart
        const ctxSales = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: salesData.labels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: salesData.data,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ₹' + context.parsed.y.toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#f3f4f6' },
                        ticks: {
                            callback: function(value) { return '₹' + value / 1000 + 'k'; }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // Invoice Status Chart
        const ctxInvoice = document.getElementById('invoiceStatusChart').getContext('2d');
        new Chart(ctxInvoice, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Pending', 'Overdue'],
                datasets: [{
                    data: [
                        invoiceStats.paid || 0,
                        (invoiceStats.sent || 0) + (invoiceStats.partially_paid || 0) + (invoiceStats.draft || 0),
                        invoiceStats.overdue || 0
                    ],
                    backgroundColor: ['#10b981', '#3b82f6', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 20 }
                    }
                }
            }
        });
    </script>
