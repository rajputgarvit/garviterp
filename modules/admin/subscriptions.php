<?php
$pageTitle = 'Subscription Management';
$currentPage = 'subscriptions';
require_once '../../config/config.php';
require_once '../../includes/admin_layout.php';
require_once '../../classes/Subscription.php';

$db = Database::getInstance();

// Helpers
function getFeatureValue($planFeatures, $planId, $featureCode) {
    if (isset($planFeatures[$planId][$featureCode])) {
        $f = $planFeatures[$planId][$featureCode];
        if ($f['limit_value'] !== null) return $f['limit_value'];
        return $f['is_enabled'] ? 'Yes' : 'No';
    }
    return 'No'; // Default
}

// Handle Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'create_plan') {
                $db->insert('subscription_plans', [
                    'plan_name' => $_POST['plan_name'],
                    'plan_code' => strtolower(str_replace(' ', '_', $_POST['plan_name'])),
                    'monthly_price' => $_POST['monthly_price'],
                    'annual_price' => $_POST['annual_price'],
                    'max_users' => $_POST['max_users'],
                    'storage_gb' => $_POST['storage_gb'],
                    'is_active' => 1,
                    'display_order' => 99,
                    'features' => '[]' // Legacy column
                ]);
                $success = "New plan created successfully.";
            
            } elseif ($_POST['action'] === 'update_plan_basics') {
                $db->update('subscription_plans', [
                    'plan_name' => $_POST['plan_name'],
                    'monthly_price' => $_POST['monthly_price'],
                    'annual_price' => $_POST['annual_price'],
                    'max_users' => $_POST['max_users'],
                    'storage_gb' => $_POST['storage_gb'],
                ], 'id = ?', [$_POST['plan_id']]);
                
                // Also update the limit in plan_features for consistency if they exist
                 $db->query("UPDATE plan_features SET limit_value = ? WHERE plan_id = ? AND feature_code = 'max_users'", [$_POST['max_users'], $_POST['plan_id']]);
                 $db->query("UPDATE plan_features SET limit_value = ? WHERE plan_id = ? AND feature_code = 'storage_gb'", [$_POST['storage_gb'], $_POST['plan_id']]);
                
                $success = "Plan updated successfully.";

            } elseif ($_POST['action'] === 'update_feature_matrix') {
                // Determine Plan ID and Code
                $planId = $_POST['plan_id'];
                $featureCode = $_POST['feature_code'];
                $value = $_POST['value']; // "1", "0", or numeric limit
                
                // Logic: Check if row exists
                $exists = $db->fetchOne("SELECT id FROM plan_features WHERE plan_id = ? AND feature_code = ?", [$planId, $featureCode]);
                
                $featureDef = $db->fetchOne("SELECT * FROM feature_definitions WHERE feature_code = ?", [$featureCode]);
                
                if ($value === 'on') $value = 1; // Checkbox behavior
                if ($value === '') $value = 0;
                
                $isEnabled = 1;
                $limitValue = null;
                
                if ($featureDef['is_measurable']) {
                    // For measurable, if value > 0, enabled = 1, limit = value. If 0, enabled = 0.
                    // Special case: -1 for unlimited? Let's assume text input
                    if (is_numeric($value)) {
                         $isEnabled = 1;
                         $limitValue = $value;
                    } else {
                        // "Unlimited" or text
                        if (strtolower($value) == 'unlimited') {
                            $limitValue = null; 
                            $isEnabled = 1;
                        } else {
                            $limitValue = (int)$value;
                        }
                    }
                } else {
                    // Boolean feature
                    $isEnabled = (int)$value;
                }

                if ($exists) {
                    $db->update('plan_features', [
                        'is_enabled' => $isEnabled,
                        'limit_value' => $limitValue
                    ], 'id = ?', [$exists['id']]);
                } else {
                    $db->insert('plan_features', [
                        'plan_id' => $planId,
                        'feature_code' => $featureCode,
                        'feature_name' => $featureDef['feature_name'], // Denormalized name
                        'feature_category' => $featureDef['category'],
                        'is_enabled' => $isEnabled,
                        'limit_value' => $limitValue
                    ]);
                }
                $success = "Feature updated.";
            } elseif ($_POST['action'] === 'delete_plan') {
                 $db->update('subscription_plans', ['is_active' => 0], 'id = ?', [$_POST['plan_id']]);
                 $success = "Plan archived.";
            } elseif ($_POST['action'] === 'approve_request') {
                $sub = new Subscription();
                // Default start today, end based on cycle
                $start = date('Y-m-d H:i:s');
                $cycle = $_POST['billing_cycle'];
                $end = ($cycle === 'annual') ? date('Y-m-d H:i:s', strtotime('+1 year')) : date('Y-m-d H:i:s', strtotime('+1 month'));
                
                if ($sub->approveRequest($_POST['request_id'], $_SESSION['user_id'], $start, $end)) {
                    $success = "Subscription approved and assigned.";
                } else {
                    $error = "Failed to approve request.";
                }
            } elseif ($_POST['action'] === 'reject_request') {
                $sub = new Subscription();
                $sub->rejectRequest($_POST['request_id'], $_SESSION['user_id']);
                $success = "Request rejected.";
            } elseif ($_POST['action'] === 'invite_user') {
                $requestId = $_POST['request_id'];
                
                // 1. Generate Token
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));
                
                // 2. Update Request
                $db->update('contact_requests', [
                    'onboarding_token' => $token,
                    'token_expires_at' => $expiresAt,
                    'status' => 'Replied'
                ], 'id = ?', [$requestId]);
                
                // 3. Fetch Details
                $request = $db->fetchOne("SELECT * FROM contact_requests WHERE id = ?", [$requestId]);
                
                // 4. Send Email
                require_once '../../classes/Mail.php';
                $mail = new Mail();
                $setupLink = BASE_URL . "public/setup.php?token=" . $token;
                
                $subject = "Your Account Setup - " . APP_NAME;
                $body = "
                    <p>Hello {$request['name']},</p>
                    <p>Good news! Your request for the plan has been approved.</p>
                    <p>Please click the link below to set up your company account and password:</p>
                    <p><a href='{$setupLink}' style='background:#007bff; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px;'>Set Up My Account</a></p>
                    <p>Or copy this link: <br>{$setupLink}</p>
                    <p>This link is valid for 48 hours.</p>
                    <br>
                    <p>Best Regards,<br>" . APP_NAME . " Team</p>
                ";
                
                if ($mail->sendWithResend($request['email'], $subject, $body)) {
                    $success = "Invitation sent to {$request['email']}.";
                } else {
                    $error = "Invitation generated but email failed to send.";
                }

            } elseif ($_POST['action'] === 'resolve_inquiry') {
                $db->update('contact_requests', ['status' => 'Resolved'], 'id = ?', [$_POST['request_id']]);
                $success = "Inquiry marked as resolved.";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Fetch Data
$plans = $db->fetchAll("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY monthly_price ASC");
$featureDefs = $db->fetchAll("SELECT * FROM feature_definitions WHERE is_active = 1 ORDER BY category DESC, display_order ASC");
$sub = new Subscription();
$pendingRequests = $sub->getPendingRequests();

// Group Features
$groupedFeatures = [];
foreach ($featureDefs as $f) {
    $groupedFeatures[$f['category']][] = $f;
}

// Fetch all plan features for matrix
$pfRaw = $db->fetchAll("SELECT * FROM plan_features");
$planFeatures = [];
foreach ($pfRaw as $row) {
    $planFeatures[$row['plan_id']][$row['feature_code']] = $row;
}

?>

<style>
    .matrix-table th, .matrix-table td { text-align: center; vertical-align: middle; }
    .matrix-table td:first-child { text-align: left; font-weight: 500; }
    .matrix-input { width: 80px; text-align: center; padding: 4px; border: 1px solid #ddd; border-radius: 4px; }
    .category-header { background: #f8fafc; font-weight: bold; text-transform: uppercase; font-size: 0.85rem; color: #64748b; letter-spacing: 0.5px; }
    .nav-tabs { display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; }
    .nav-item { padding: 10px 20px; cursor: pointer; color: #64748b; font-weight: 500; border-bottom: 2px solid transparent; margin-bottom: -2px; }
    .nav-item.active { color: #2563eb; border-bottom-color: #2563eb; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
</style>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if (isset($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">Subscription Management</div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPlanModal">
            <i class="fas fa-plus"></i> New Plan
        </button>
    </div>

    <!-- Tabs -->
    <div class="nav-tabs">
        <div class="nav-item active" onclick="switchTab('requests')">Requests <?php if(count($pendingRequests) > 0) echo '<span class="badge bg-danger rounded-pill ms-1">'.count($pendingRequests).'</span>'; ?></div>
        <div class="nav-item" onclick="switchTab('plans')">Active Plans</div>
        <div class="nav-item" onclick="switchTab('matrix')">Feature Matrix</div>
    </div>

    <!-- Requests Tab -->
    <div id="requests-tab" class="tab-content active">
        <?php 
            // Fetch Guest Requests (moved up for unified empty check)
            $guestRequests = $db->fetchAll("SELECT * FROM contact_requests WHERE subject LIKE 'New%Plan Request%' AND status = 'New' ORDER BY created_at DESC");
            
            $hasAnyRequests = !empty($pendingRequests) || !empty($guestRequests);
        ?>

        <?php if (!$hasAnyRequests): ?>
            <div class="p-5 text-center text-muted">
                <i class="fas fa-check-circle fa-3x mb-3" style="color: #cbd5e1;"></i>
                <p>No pending subscription requests.</p>
            </div>
        <?php else: ?>
            
            <!-- Registered User Requests -->
            <?php if (!empty($pendingRequests)): ?>
                <h5 class="mb-3 text-secondary">Upgrade Requests (Registered Users)</h5>
                <div class="table-responsive mb-5">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Company</th>
                                <th>User</th>
                                <th>Requested Plan</th>
                                <th>Cycle</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingRequests as $req): ?>
                                <tr>
                                    <td><?php echo date('d M, h:i A', strtotime($req['request_date'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($req['company_name']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($req['user_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($req['user_email']); ?></small>
                                    </td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($req['plan_name']); ?></span></td>
                                    <td><?php echo ucfirst($req['billing_cycle']); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Approve and activate this subscription?');">
                                            <input type="hidden" name="action" value="approve_request">
                                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                            <input type="hidden" name="billing_cycle" value="<?php echo $req['billing_cycle']; ?>">
                                            <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button>
                                        </form>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Reject this request?');">
                                            <input type="hidden" name="action" value="reject_request">
                                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i> Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Guest Requests Section -->
            <?php if (!empty($guestRequests)): ?>
                <h5 class="mb-3 text-secondary">Guest Plan Requests (Require Account Creation)</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Contact Info</th>
                                <th>Requested Plan</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($guestRequests as $gReq): ?>
                                <tr>
                                    <td><?php echo date('d M, h:i A', strtotime($gReq['created_at'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($gReq['name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($gReq['email']); ?></small><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($gReq['subject']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">Guest Inquiry</span>
                                    </td>
                                    <td><span class="badge bg-warning text-dark"><?php echo $gReq['status']; ?></span></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Send invitation email with setup link?');">
                                            <input type="hidden" name="action" value="invite_user">
                                            <input type="hidden" name="request_id" value="<?php echo $gReq['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-envelope"></i> Approve & Invite
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Mark this inquiry as resolved?');">
                                            <input type="hidden" name="action" value="resolve_inquiry">
                                            <input type="hidden" name="request_id" value="<?php echo $gReq['id']; ?>">
                                            <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-check"></i> Mark Done</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>

    <!-- Plans Tab -->
    <div id="plans-tab" class="tab-content">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Plan Name</th>
                        <th>Code</th>
                        <th>Price (Monthly)</th>
                        <th>Price (Annual)</th>
                        <th>Limits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plans as $plan): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($plan['plan_name']); ?></strong></td>
                        <td><code><?php echo htmlspecialchars($plan['plan_code']); ?></code></td>
                        <td>₹<?php echo number_format($plan['monthly_price']); ?></td>
                        <td>₹<?php echo number_format($plan['annual_price']); ?></td>
                        <td>
                            <div style="font-size: 0.9em; color: #666;">
                                <i class="fas fa-users"></i> <?php echo $plan['max_users']; ?> Users<br>
                                <i class="fas fa-hdd"></i> <?php echo $plan['storage_gb']; ?> GB
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick='openEditPlan(<?php echo json_encode($plan); ?>)'><i class="fas fa-edit"></i> Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Archive this plan?');">
                                <input type="hidden" name="action" value="delete_plan">
                                <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Feature Matrix Tab -->
    <div id="matrix-tab" class="tab-content">
        <div class="table-responsive">
            <table class="matrix-table table-bordered">
                <thead>
                    <tr>
                        <th style="min-width: 250px;">Feature / Module</th>
                        <?php foreach ($plans as $plan): ?>
                            <th><?php echo htmlspecialchars($plan['plan_name']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupedFeatures as $category => $features): ?>
                        <tr class="category-header"><td colspan="<?php echo count($plans) + 1; ?>"><?php echo ucfirst($category); ?>s</td></tr>
                        <?php foreach ($features as $f): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($f['feature_name']); ?>
                                    <i class="fas fa-info-circle text-muted" title="<?php echo htmlspecialchars($f['description']); ?>" style="margin-left: 5px; cursor: help;"></i>
                                </td>
                                <?php foreach ($plans as $plan): ?>
                                    <td>
                                        <?php 
                                            // Determine current value
                                            $isEnabled = false;
                                            $val = '';
                                            if (isset($planFeatures[$plan['id']][$f['feature_code']])) {
                                                $pf = $planFeatures[$plan['id']][$f['feature_code']];
                                                $isEnabled = (bool)$pf['is_enabled'];
                                                $val = $pf['limit_value'];
                                            }
                                            
                                            if ($f['is_measurable']) {
                                                // Input field for limits (auto-saves on change)
                                                // If NULL limit but enabled, it means unlimited
                                                $displayVal = $val;
                                                if ($isEnabled && $val === null) $displayVal = 'Unlimited';
                                                // If not enabled, maybe show 0? Or just empty.
                                                if (!$isEnabled) $displayVal = 0;
                                                
                                                echo '<input type="text" class="matrix-input" 
                                                        value="'.htmlspecialchars($displayVal).'" 
                                                        onblur="updateMatrix('.$plan['id'].', \''.$f['feature_code'].'\', this.value)">';
                                            } else {
                                                // Checkbox for boolean
                                                $checked = $isEnabled ? 'checked' : '';
                                                echo '<label class="switch-sm">';
                                                echo '<input type="checkbox" '.$checked.' onchange="updateMatrix('.$plan['id'].', \''.$f['feature_code'].'\', this.checked ? 1 : 0)">';
                                                echo '<span class="slider round"></span>';
                                                echo '</label>';
                                            }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 20px; text-align: right; color: #64748b; font-size: 0.9em;">
            <i class="fas fa-save"></i> Changes are saved automatically when you leave the field.
        </div>
    </div>
</div>

<!-- Create Plan Modal -->
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_plan">
                    <div class="mb-3">
                        <label class="form-label">Plan Name</label>
                        <input type="text" name="plan_name" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col">
                            <label class="form-label">Monthly Price</label>
                            <input type="number" name="monthly_price" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Annual Price</label>
                            <input type="number" name="annual_price" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                         <div class="col">
                            <label class="form-label">Max Users</label>
                            <input type="number" name="max_users" class="form-control" value="1" required>
                        </div>
                         <div class="col">
                            <label class="form-label">Storage (GB)</label>
                            <input type="number" name="storage_gb" class="form-control" value="1" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Plan Modal -->
<div class="modal fade" id="editPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Plan Basics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_plan_basics">
                    <input type="hidden" name="plan_id" id="edit_plan_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Plan Name</label>
                        <input type="text" name="plan_name" id="edit_plan_name" class="form-control" required>
                    </div>
                    
                    <div class="row g-3 mb-3">
                         <div class="col">
                            <label class="form-label">Monthly (₹)</label>
                            <input type="number" name="monthly_price" id="edit_monthly" class="form-control" required>
                        </div>
                         <div class="col">
                            <label class="form-label">Annual (₹)</label>
                            <input type="number" name="annual_price" id="edit_annual" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                         <div class="col">
                            <label class="form-label">Max Users</label>
                            <input type="number" name="max_users" id="edit_users" class="form-control" required>
                        </div>
                         <div class="col">
                            <label class="form-label">Storage (GB)</label>
                            <input type="number" name="storage_gb" id="edit_storage" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    
    document.getElementById(tabId + '-tab').classList.add('active');
    event.target.classList.add('active');
}

function openEditPlan(plan) {
    document.getElementById('edit_plan_id').value = plan.id;
    document.getElementById('edit_plan_name').value = plan.plan_name;
    document.getElementById('edit_monthly').value = plan.monthly_price;
    document.getElementById('edit_annual').value = plan.annual_price;
    document.getElementById('edit_users').value = plan.max_users;
    document.getElementById('edit_storage').value = plan.storage_gb;
    
    var myModal = new bootstrap.Modal(document.getElementById('editPlanModal'));
    myModal.show();
}

function updateMatrix(planId, featureCode, value) {
    // Send AJAX request to update
    const formData = new FormData();
    formData.append('action', 'update_feature_matrix');
    formData.append('plan_id', planId);
    formData.append('feature_code', featureCode);
    formData.append('value', value);
    
    fetch('subscriptions.php', {
        method: 'POST',
        body: formData
    }).then(res => res.text()).then(res => {
        // Optional: Show toast
        console.log('Updated');
    });
}
</script>

</div> <!-- End content-area -->
</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>

