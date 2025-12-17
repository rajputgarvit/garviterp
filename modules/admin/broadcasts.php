<?php
$pageTitle = 'System Broadcasts';
$currentPage = 'broadcasts';
require_once '../../config/config.php';
require_once '../../includes/admin_layout.php';
require_once '../../classes/Broadcast.php';

$broadcast = new Broadcast();
$auth = new Auth();
$user = $auth->getCurrentUser();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_broadcast'])) {
        $broadcast->create(
            $_POST['title'],
            $_POST['message'],
            $_POST['type'],
            $_POST['start_date'],
            $_POST['end_date'],
            $user['id'],
            $_POST['target_company_id'] ?? null
        );
        $success = "Broadcast successfully published to the network.";
    } elseif (isset($_POST['delete_id'])) {
        $broadcast->delete($_POST['delete_id']);
        $success = "Broadcast removed.";
    } elseif (isset($_POST['toggle_id'])) {
        $broadcast->toggleStatus($_POST['toggle_id']);
        $success = "Broadcast visibility updated.";
    }
}

$allBroadcasts = $broadcast->getAll();
?>

<style>
    :root {
        --color-info: #3b82f6;
        --color-warning: #f59e0b;
        --color-danger: #ef4444;
        --color-success: #10b981;
    }
    
    .page-hero {
        background: linear-gradient(135deg, #1e1e2d 0%, #2d2d42 100%);
        color: white;
        padding: 40px;
        border-radius: 16px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        position: relative;
        overflow: hidden;
    }
    
    .page-hero::after {
        content: '';
        position: absolute;
        top: 0; right: 0; bottom: 0;
        width: 30%;
        background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMTAwIDBSMTAwIDIwMCBMMCAxMDAgWiIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjAzKSIvPjwvc3ZnPg==') no-repeat right center;
        background-size: cover;
        z-index: 0;
    }
    
    .page-hero > * {
        position: relative;
        z-index: 1;
    }

    .glass-card {
        background: white;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        transition: transform 0.2s, box-shadow 0.2s;
        overflow: hidden;
    }

    .broadcast-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
    }

    .broadcast-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; bottom: 0; width: 4px;
        background: #ccc;
    }
    
    .broadcast-card.type-info::before { background: var(--color-info); }
    .broadcast-card.type-warning::before { background: var(--color-warning); }
    .broadcast-card.type-danger::before { background: var(--color-danger); }
    .broadcast-card.type-success::before { background: var(--color-success); }

    .status-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-live { background: #dcfce7; color: #166534; }
    .status-disabled { background: #f3f4f6; color: #6b7280; }
    .status-expired { background: #fef9c3; color: #854d0e; }

    .icon-box {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: #f1f5f9;
        color: #64748b;
        font-size: 1.2rem;
    }

    .meta-tag {
        font-size: 0.85rem;
        color: #64748b;
        display: flex; align-items: center; gap: 5px;
    }

    .form-control-lg-custom {
        padding: 12px 15px;
        font-size: 1rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    
    .form-control-lg-custom:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
</style>

<div class="container-fluid p-4">
    
    <!-- Hero Section -->
    <div class="page-hero d-flex justify-content-between align-items-center">
        <div>
            <h1 class="font-weight-bold mb-2">System Broadcasts</h1>
            <p class="mb-0 opacity-75">Announce important updates, maintenance alerts, or news to your entire organization instantly.</p>
        </div>
        <button class="btn btn-primary btn-lg shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#createFormCollapse">
            <i class="fas fa-plus me-2"></i> New Broadcast
        </button>
    </div>

    <!-- Notifications -->
    <?php if (isset($success)): ?>
        <div class="alert alert-success d-flex align-items-center mb-4 shadow-sm" role="alert" style="border-radius: 12px;">
            <i class="fas fa-check-circle me-3 fs-4"></i>
            <div><?php echo $success; ?></div>
        </div>
    <?php endif; ?>

    <!-- Create Form (Collapsed) -->
    <div class="collapse mb-5" id="createFormCollapse">
        <div class="glass-card p-0 overflow-hidden">
            <div class="p-4 border-bottom bg-light">
                <h5 class="m-0 text-primary"><i class="fas fa-pen-nib me-2"></i> Compose Announcement</h5>
            </div>
            <div class="p-4">
                <form method="POST">
                    <input type="hidden" name="create_broadcast" value="1">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-9">
                            <label class="form-label fw-bold text-dark small text-uppercase">Subject</label>
                            <input type="text" name="title" class="form-control form-control-lg shadow-none border-secondary" style="border-width: 1px;" required placeholder="e.g., Scheduled System Maintenance">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark small text-uppercase">Alert Type</label>
                            <select name="type" class="form-select form-control-lg shadow-none border-secondary" style="border-width: 1px;">
                                <option value="info">ℹ️ Info (Blue)</option>
                                <option value="success">✅ Success (Green)</option>
                                <option value="warning">⚠️ Warning (Yellow)</option>
                                <option value="danger">🚨 Danger (Red)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Target Audience & Message -->
                    <div class="mb-4">
                         <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small text-uppercase">Target Audience</label>
                                <select name="target_company_id" class="form-select shadow-none border-secondary" style="border-width: 1px;">
                                    <option value="">🌍 Global (All Companies)</option>
                                    <?php 
                                        // Fetch companies for dropdown
                                        $db = Database::getInstance();
                                        $companies = $db->fetchAll("SELECT id, company_name FROM company_settings ORDER BY company_name ASC");
                                        foreach ($companies as $c) {
                                            echo '<option value="' . $c['id'] . '">🏢 ' . htmlspecialchars($c['company_name']) . '</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                         </div>

                        <label class="form-label fw-bold text-dark small text-uppercase">Message Content</label>
                        <textarea name="message" class="form-control shadow-none border-secondary" style="border-width: 1px; min-height: 150px; resize: vertical;" required placeholder="Write your announcement details here..."></textarea>
                    </div>

                    <!-- Row 3: Schedule and Actions -->
                    <div class="p-3 bg-light rounded-3 d-flex flex-column flex-md-row gap-3 align-items-end justify-content-between">
                        <div class="d-flex gap-3 flex-grow-1 w-100">
                             <div class="flex-grow-1">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="far fa-calendar-alt me-1"></i> Start Showing</label>
                                <input type="datetime-local" name="start_date" class="form-control form-control-sm border-0 shadow-sm" required value="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="far fa-clock me-1"></i> Stop Showing</label>
                                <input type="datetime-local" name="end_date" class="form-control form-control-sm border-0 shadow-sm" required value="<?php echo date('Y-m-d\TH:i', strtotime('+1 day')); ?>">
                            </div>
                        </div>
                        <div class="w-100 w-md-auto text-end">
                             <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                                <i class="fas fa-paper-plane me-2"></i> Publish Announcement
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Broadcasts Feed -->
    <div class="row g-4">
        <?php if (empty($allBroadcasts)): ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted opacity-50 mb-3"><i class="fas fa-bullhorn fa-4x"></i></div>
                <h4>No broadcasts yet</h4>
                <p class="text-muted">Create your first announcement to reach your users.</p>
            </div>
        <?php else: ?>
            <?php foreach ($allBroadcasts as $b): ?>
                <?php 
                    $now = date('Y-m-d H:i:s');
                    $isActive = $b['is_active'] && $b['start_date'] <= $now && $b['end_date'] >= $now;
                    $statusClass = $isActive ? 'status-live' : ($b['is_active'] ? 'status-expired' : 'status-disabled');
                    $statusText = $isActive ? 'Live' : ($b['is_active'] ? 'Pending / Expired' : 'Disabled');
                    
                    $icon = match($b['type']) {
                        'danger' => 'fa-exclamation-triangle',
                        'warning' => 'fa-bell',
                        'success' => 'fa-check-circle',
                        default => 'fa-info-circle'
                    };
                    $colorVar = 'var(--color-' . $b['type'] . ')';
                ?>
                <div class="col-md-6 col-xl-4">
                    <div class="glass-card broadcast-card type-<?php echo $b['type']; ?> p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box" style="color: <?php echo $colorVar; ?>; background: color-mix(in srgb, <?php echo $colorVar; ?> 10%, white);">
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($b['title']); ?></h5>
                                    <span class="status-badge <?php echo $statusClass; ?> me-2"><?php echo $statusText; ?></span>
                                    <?php if (!empty($b['target_company_id'])): ?>
                                        <span class="badge bg-secondary text-white" title="Targeted to: <?php echo htmlspecialchars($b['company_name']); ?>"><i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($b['company_name']); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-primary text-white" title="Global Broadcast"><i class="fas fa-globe me-1"></i> Global</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                    <li>
                                        <form method="POST">
                                            <input type="hidden" name="toggle_id" value="<?php echo $b['id']; ?>">
                                            <button class="dropdown-item" type="submit">
                                                <i class="fas fa-power-off me-2"></i> <?php echo $b['is_active'] ? 'Disable' : 'Enable'; ?>
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" onsubmit="return confirm('Delete this broadcast?');">
                                            <input type="hidden" name="delete_id" value="<?php echo $b['id']; ?>">
                                            <button class="dropdown-item text-danger" type="submit">
                                                <i class="fas fa-trash me-2"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="flex-grow-1 mb-4">
                            <p class="text-secondary"><?php echo nl2br(htmlspecialchars($b['message'])); ?></p>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div class="meta-tag" title="Valid From">
                                <i class="far fa-calendar-alt"></i> 
                                <?php echo date('M d, H:i', strtotime($b['start_date'])); ?>
                            </div>
                            <div class="meta-tag" title="Valid Until">
                                <i class="far fa-clock"></i> 
                                <?php echo date('M d, H:i', strtotime($b['end_date'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>




<?php require_once '../../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manual fallback for collapse toggle
    const toggleBtn = document.querySelector('[data-bs-target="#createFormCollapse"]');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent defaults
            const targetId = this.getAttribute('data-bs-target');
            const targetEl = document.querySelector(targetId);
            if (targetEl && window.bootstrap) {
                // Try BS API first
                const collapse = new bootstrap.Collapse(targetEl, { toggle: true });
            } else if (targetEl) {
                // Manual fallback class toggle
                targetEl.classList.toggle('show');
            }
        });
    }
});
</script>
