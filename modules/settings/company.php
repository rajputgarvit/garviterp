<?php
// session_start(); // Handled in config.php
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Database.php';
require_once '../../classes/ReferenceData.php';

$auth = new Auth();
// Auth::enforceGlobalRouteSecurity() handles permissions.
$db = Database::getInstance();
$user = $auth->getCurrentUser();
$refData = new ReferenceData();

$states = $refData->getStates();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $action = $_POST['action'];
        
        if ($action === 'update_company_settings') {
            $companyId = $_POST['company_id'] ?? null;
            
            $data = [
                // Company Profile
                'company_name' => $_POST['company_name'],
                'industry_type' => $_POST['industry_type'] ?? null,
                'business_type' => $_POST['business_type'] ?? null,
                'company_registration_number' => $_POST['company_registration_number'] ?? null,
                'address_line1' => $_POST['address_line1'],
                'address_line2' => $_POST['address_line2'] ?? null,
                'city' => $_POST['city'],
                'state' => $_POST['state'],
                'country' => $_POST['country'] ?? 'India',
                'postal_code' => $_POST['postal_code'] ?? null,
                'phone' => $_POST['phone'] ?? null,
                'email' => $_POST['email'] ?? null,
                'website' => $_POST['website'] ?? null,
                
                // Tax Information
                'gstin' => $_POST['gstin'] ?? null,
                'pan' => $_POST['pan'] ?? null,
                'tax_registration_date' => !empty($_POST['tax_registration_date']) ? $_POST['tax_registration_date'] : null,
                
                // Bank Details
                'bank_name' => $_POST['bank_name'] ?? null,
                'bank_account_number' => $_POST['bank_account_number'] ?? null,
                'bank_ifsc' => $_POST['bank_ifsc'] ?? null,
                'bank_branch' => $_POST['bank_branch'] ?? null,
                'bank_account_holder' => $_POST['bank_account_holder'] ?? null,
                
                // Business Settings
                'financial_year_start' => $_POST['financial_year_start'],
                'currency_code' => $_POST['currency_code'],
                'currency_symbol' => $_POST['currency_symbol'],
                'date_format' => $_POST['date_format'],
                'timezone' => $_POST['timezone'],
                
                // Invoice Settings
                'invoice_prefix' => $_POST['invoice_prefix'],
                'quotation_prefix' => $_POST['quotation_prefix'],
                'invoice_due_days' => $_POST['invoice_due_days'],
                'terms_conditions' => $_POST['terms_conditions'],
                'invoice_footer' => $_POST['invoice_footer'] ?? null,
                'print_logo_on_invoice' => isset($_POST['print_logo_on_invoice']) ? 1 : 0,
                
                // Social Media
                'linkedin_url' => $_POST['linkedin_url'] ?? null,
                'facebook_url' => $_POST['facebook_url'] ?? null,
                'twitter_url' => $_POST['twitter_url'] ?? null,
                'instagram_url' => $_POST['instagram_url'] ?? null,
                
                'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? 1 : 0,
                
                // System Preferences
                'low_stock_threshold' => $_POST['low_stock_threshold'],
                'enable_multi_currency' => isset($_POST['enable_multi_currency']) ? 1 : 0,
                'enable_barcode' => isset($_POST['enable_barcode']) ? 1 : 0,
                'is_gst_registered' => isset($_POST['is_gst_registered']) ? 1 : 0,
                'enable_einvoicing' => isset($_POST['enable_einvoicing']) ? 1 : 0,
                
                // Branding
                'app_name' => $_POST['app_name'] ?? null,
                'theme_color' => $_POST['theme_color'] ?? '#3b82f6'
            ];

            // Handle Logo Upload
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/logos/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExtension = strtolower(pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $fileName = 'logo_' . $user['company_id'] . '_' . time() . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $uploadPath)) {
                         $data['logo_path'] = 'public/uploads/logos/' . $fileName;
                    }
                }
            }
            
            // Ensure we are updating the correct company settings based on logged-in user
            $targetCompanyId = $user['company_id'];
            
            if ($targetCompanyId) {
                $db->update('company_settings', $data, 'id = ?', [$targetCompanyId]);
            } else {
                // This should not happen for logged-in users with valid company_id
                $db->insert('company_settings', $data);
            }
            
            $success = "Company settings updated successfully!";
             // Refresh data
             $companySettings = $db->fetchOne("SELECT * FROM company_settings WHERE id = ? LIMIT 1", [$user['company_id']]);

        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Fetch company settings if not already fetched
if (!isset($companySettings)) {
    $companySettings = $db->fetchOne("SELECT * FROM company_settings WHERE id = ? LIMIT 1", [$user['company_id']]);
}

// Set defaults if no settings exist
if (!$companySettings) {
    $companySettings = [
        'financial_year_start' => 4,
        'currency_code' => 'INR',
        'currency_symbol' => '₹',
        'date_format' => 'd-m-Y',
        'timezone' => 'Asia/Kolkata',
        'invoice_prefix' => 'INV',
        'quotation_prefix' => 'QT',
        'invoice_due_days' => 30,
        'low_stock_threshold' => 10,
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
        'enable_email_notifications' => 1,
        'enable_barcode' => 1,
        'enable_multi_currency' => 0,
        'is_gst_registered' => 0,
        'enable_einvoicing' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Settings - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-bg: #f3f4f6;
            --border-color: #e5e7eb;
            --input-border: #d1d5db;
        }

        body {
            background-color: var(--primary-bg);
            font-family: 'Inter', sans-serif;
            color: #1f2937;
        }

        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Top Action Bar */
        .page-header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            background: white;
            padding: 16px 24px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .header-title h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            color: #111827;
        }

        .header-title p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #6b7280;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-action-primary {
            background-color: #7c3aed; /* Purple brand color from reference */
            color: white;
            padding: 10px 24px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-action-primary:hover {
            background-color: #6d28d9;
        }

        .btn-action-light {
            background-color: white;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
        }
        
        .btn-action-light:hover {
            background-color: #f9fafb;
        }

        /* Main Form Card */
        .settings-card {
            background: white;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        /* Form Styling (Reference UI) */
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .form-label.required::after {
            content: " *";
            color: #ef4444;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            line-height: 1.5;
            color: #1f2937;
            background-color: #fff;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #7c3aed;
            outline: none;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        textarea.form-control {
            height: auto;
            min-height: 80px;
        }

        /* Fix Dropdown Text Clipping & Appearance */
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem; /* Space for arrow */
            line-height: 1.5; /* Ensure text centering */
        }

        /* Logo Upload Section - Specific Grid */
        .logo-section-grid {
            display: grid;
            grid-template-columns: 200px 1fr 1fr;
            gap: 24px;
            margin-bottom: 30px;
        }

        .logo-upload-container {
            border: 2px dashed #93c5fd; /* Soft blue dashed border */
            background-color: #eff6ff;
            border-radius: 8px;
            height: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
        }

        .logo-upload-container:hover {
            border-color: #3b82f6;
            background-color: #eedeff;
        }

        .logo-upload-container i {
            font-size: 24px;
            color: #3b82f6;
            margin-bottom: 8px;
        }

        .logo-upload-container span {
            font-size: 13px;
            color: #2563eb;
            font-weight: 500;
            text-align: center;
        }
        
        .logo-upload-container small {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .logo-upload-container input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2; /* Ensure it's on top */
        }

        .logo-preview-img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
        }

        /* Two columns grid for general fields */
        .grid-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .d-full-width {
            width: 100%;
            margin-bottom: 24px;
        }
        
        .section-separator {
            margin: 30px 0;
            border-top: 1px solid #e5e7eb;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 20px;
        }

        /* Toggle Switches */
        .toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #fafafa;
        }

        /* Custom Radio for GST */
        .radio-group {
            display: flex;
            gap: 20px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .radio-option input[type="radio"] {
            accent-color: #7c3aed;
            width: 18px;
            height: 18px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .logo-section-grid {
                grid-template-columns: 1fr;
            }
            
            .grid-row-2 {
                grid-template-columns: 1fr;
            }
            
            .page-header-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include INCLUDES_PATH . '/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include INCLUDES_PATH . '/header.php'; ?>
            
            
            <form method="POST" enctype="multipart/form-data" action="company">
                <input type="hidden" name="action" value="update_company_settings">
                <input type="hidden" name="company_id" value="<?php echo $companySettings['id'] ?? ''; ?>">

                <div class="settings-container">
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Action Header (Top Right Buttons) -->
                    <div class="page-header-actions">
                        <div class="header-title">
                            <h1>Business Settings</h1>
                            <p>Edit Your Company Settings And Information</p>
                        </div>
                        <div class="action-buttons">
                            <button type="button" class="btn-action-light">
                                <i class="fas fa-headset me-2"></i> Chat Support
                            </button>
                            <a href="settings.php" class="btn-action-light">
                                Cancel
                            </a>
                            <button type="submit" class="btn-action-primary">
                                Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- Main Content Card -->
                    <div class="settings-card">
                        
                        <!-- Top Grid: Logo + Name + Type -->
                        <div class="logo-section-grid">
                            <!-- Col 1: Logo -->
                            <div class="logo-upload-container">
                                <?php if (!empty($companySettings['logo_path'])): ?>
                                    <img src="../../<?php echo htmlspecialchars($companySettings['logo_path']); ?>" alt="Logo" class="logo-preview-img">
                                <?php else: ?>
                                    <i class="fas fa-image"></i>
                                    <span>Upload Logo</span>
                                    <small>PNG/JPG, max 5MB</small>
                                <?php endif; ?>
                                <input type="file" id="company_logo" name="company_logo" accept="image/*">
                            </div>

                            <!-- Col 2: Business Name (Spans rest of space handled in grid) -->
                            <div style="grid-column: span 2;">
                                <div class="mb-4">
                                    <label class="form-label required">Business Name</label>
                                    <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($companySettings['company_name'] ?? ''); ?>" placeholder="Enter Business Name" required>
                                </div>
                                <div class="grid-row-2 mb-0">
                                    <div>
                                        <label class="form-label">Industry Type</label>
                                        <select class="form-select form-control" name="industry_type">
                                            <option value="">Select Industry Type</option>
                                            <?php
                                            $industries = [
                                                'Retail', 'Wholesale', 'Manufacturing', 'Services', 'IT & Software', 
                                                'Healthcare', 'Education', 'Construction', 'Logistics', 'Agriculture', 'Other'
                                            ];
                                            foreach ($industries as $ind) {
                                                $selected = ($companySettings['industry_type'] ?? '') === $ind ? 'selected' : '';
                                                echo "<option value=\"$ind\" $selected>$ind</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div>
                                         <label class="form-label">Business Registration Type</label>
                                        <select class="form-select form-control" name="business_type">
                                            <option value="">Select Type</option>
                                            <?php
                                            $govTypes = ['Private Limited Company', 'Sole Proprietorship', 'Partnership Firm', 'LLP', 'Individual', 'Not Registered'];
                                            foreach ($govTypes as $type) {
                                                $selected = ($companySettings['business_type'] ?? '') === $type ? 'selected' : '';
                                                echo "<option value=\"$type\" $selected>$type</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Row -->
                        <div class="grid-row-2">
                            <div>
                                <label class="form-label">Company Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($companySettings['phone'] ?? ''); ?>" placeholder="Enter Phone Number">
                            </div>
                            <div>
                                <label class="form-label">Company E-Mail</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($companySettings['email'] ?? ''); ?>" placeholder="Enter Company Email">
                            </div>
                        </div>

                        <!-- Billing Address -->
                        <div class="d-full-width">
                            <label class="form-label">Company Address</label>
                            <textarea name="address_line1" class="form-control" placeholder="Enter Company Address"><?php echo htmlspecialchars($companySettings['address_line1'] ?? ''); ?></textarea>
                        </div>

                        <!-- Location Row -->
                        <div class="grid-row-2">
                            <div>
                                <label class="form-label">State</label>
                                <select name="state" class="form-control">
                                    <option value="">Select State</option>
                                    <?php 
                                    $currentState = $companySettings['state'] ?? '';
                                    foreach ($states as $state): 
                                    ?>
                                        <option value="<?php echo $state['state_name']; ?>" <?php echo ($currentState == $state['state_name']) ? 'selected' : ''; ?>>
                                            <?php echo $state['state_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Pincode</label>
                                <input type="text" name="postal_code" class="form-control" value="<?php echo htmlspecialchars($companySettings['postal_code'] ?? ''); ?>" placeholder="Enter Pincode">
                            </div>
                        </div>

                        <!-- City -->
                        <div class="d-full-width">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($companySettings['city'] ?? ''); ?>" placeholder="Enter City">
                        </div>
                        
                        <!-- GST & Registration -->
                        <div class="grid-row-2 align-items-end" style="grid-template-columns: 1fr 1fr 1fr;">
                           <div>
                                <label class="form-label mb-3">Are you GST Registered?</label>
                                <div class="radio-group form-control border-0 p-0" style="height: auto;">
                                    <label class="radio-option">
                                        <input type="radio" name="is_gst_registered" value="1" <?php echo ($companySettings['is_gst_registered'] ?? 0) ? 'checked' : ''; ?> onchange="toggleGstField(true)">
                                        Yes
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="is_gst_registered" value="0" <?php echo !($companySettings['is_gst_registered'] ?? 0) ? 'checked' : ''; ?> onchange="toggleGstField(false)">
                                        No
                                    </label>
                                </div>
                             </div>
                             
                             <div id="gst-container" style="display: <?php echo ($companySettings['is_gst_registered'] ?? 0) ? 'block' : 'none'; ?>;">
                                <label class="form-label">GSTIN</label>
                                <input type="text" name="gstin" id="gstin" class="form-control" value="<?php echo htmlspecialchars($companySettings['gstin'] ?? ''); ?>" placeholder="Enter GSTIN" maxlength="15" onblur="extractPanFromGst(this.value)">
                             </div>

                             <div id="pan-container">
                                <label class="form-label required">PAN Number</label>
                                <input type="text" name="pan" id="pan" class="form-control" value="<?php echo htmlspecialchars($companySettings['pan'] ?? ''); ?>" placeholder="Enter PAN Number" maxlength="10" required>
                             </div>
                        </div>
                        
                        <!-- E-Invoicing Toggle Frame -->
                        <div class="d-full-width mt-3">
                            <div class="toggle-wrapper justify-content-between">
                                <span class="text-primary font-weight-bold">Enable e-Invoicing</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="enable_einvoicing" value="1" <?php echo ($companySettings['enable_einvoicing'] ?? 0) ? 'checked' : ''; ?> style="width: 40px; height: 20px;">
                                </div>
                            </div>
                        </div>

                        <div class="section-separator"></div>
                        
                        <div class="section-title">Bank Details</div>
                         <div class="grid-row-2">
                            <div>
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" value="<?php echo htmlspecialchars($companySettings['bank_name'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="form-label">Account Number</label>
                                <input type="text" name="bank_account_number" class="form-control" value="<?php echo htmlspecialchars($companySettings['bank_account_number'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="form-label">IFSC Code</label>
                                <input type="text" name="bank_ifsc" class="form-control" value="<?php echo htmlspecialchars($companySettings['bank_ifsc'] ?? ''); ?>">
                            </div>
                             <div>
                                <label class="form-label">Branch Name</label>
                                <input type="text" name="bank_branch" class="form-control" value="<?php echo htmlspecialchars($companySettings['bank_branch'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- Extra Business Details (Website, etc) -->
                        <div class="card bg-light border p-3 mt-4">
                            <h6 class="mb-3">Add Business Details</h6>
                            <p class="small text-muted mb-3">Add additional business information such as Website, PAN number, etc.</p>
                            
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <select class="form-select form-control">
                                        <option>Website</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" name="website" class="form-control" value="<?php echo htmlspecialchars($companySettings['website'] ?? ''); ?>" placeholder="www.website.com">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100" style="background-color: #7c3aed; border:none;">Add</button>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- End Main Card -->

                    <!-- Additional Settings Cards (Preserving Functionality) -->
                    <div class="settings-card mt-4">
                        <div class="section-title">Invoice & Fiscal Settings</div>
                        <div class="grid-row-2">
                            <div>
                                <label class="form-label">Fiscal Year Start Month</label>
                                <select name="financial_year_start" class="form-control">
                                    <?php
                                    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                    foreach ($months as $index => $month) {
                                        $monthNum = $index + 1;
                                        $selected = ($companySettings['financial_year_start'] ?? 4) == $monthNum ? 'selected' : '';
                                        echo "<option value=\"$monthNum\" $selected>$month</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Currency</label>
                                <div class="input-group">
                                    <input type="text" name="currency_code" class="form-control" value="<?php echo htmlspecialchars($companySettings['currency_code'] ?? 'INR'); ?>" placeholder="Code (INR)">
                                    <input type="text" name="currency_symbol" class="form-control" value="<?php echo htmlspecialchars($companySettings['currency_symbol'] ?? '₹'); ?>" placeholder="Symbol (₹)">
                                </div>
                            </div>
                        </div>
                        <div class="grid-row-2">
                            <div>
                                <label class="form-label">Invoice Prefix</label>
                                <input type="text" name="invoice_prefix" class="form-control" value="<?php echo htmlspecialchars($companySettings['invoice_prefix'] ?? 'INV'); ?>">
                            </div>
                            <div>
                                <label class="form-label">Quotation Prefix</label>
                                <input type="text" name="quotation_prefix" class="form-control" value="<?php echo htmlspecialchars($companySettings['quotation_prefix'] ?? 'QT'); ?>">
                            </div>
                        </div>
                        <div class="grid-row-2">
                             <div>
                                <label class="form-label">Access Barcode Scanner?</label>
                                 <div class="radio-group mt-2">
                                    <label class="radio-option">
                                        <input type="radio" name="enable_barcode" value="1" <?php echo ($companySettings['enable_barcode'] ?? 1) ? 'checked' : ''; ?>> Yes
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="enable_barcode" value="0" <?php echo !($companySettings['enable_barcode'] ?? 1) ? 'checked' : ''; ?>> No
                                    </label>
                                </div>
                            </div>
                             <div>
                                <label class="form-label">Low Stock Alert Quantity</label>
                                <input type="number" name="low_stock_threshold" class="form-control" value="<?php echo $companySettings['low_stock_threshold'] ?? 10; ?>">
                            </div>
                        </div>
                        
                         <div class="mt-3">
                            <label class="form-label">Invoice Terms & Conditions</label>
                            <textarea name="terms_conditions" class="form-control" rows="3"><?php echo htmlspecialchars($companySettings['terms_conditions'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Hidden Fields for missing data to prevent errors if not set consistently -->
                    <input type="hidden" name="date_format" value="<?php echo $companySettings['date_format'] ?? 'd-m-Y'; ?>">
                    <input type="hidden" name="timezone" value="<?php echo $companySettings['timezone'] ?? 'Asia/Kolkata'; ?>">
                    <input type="hidden" name="invoice_due_days" value="<?php echo $companySettings['invoice_due_days'] ?? 30; ?>">

                </div>
            </form>
        </main>
    </div>

    <!-- Scripts loaded via sidebar.php -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoInput = document.getElementById('company_logo');
            const logoContainer = document.querySelector('.logo-upload-container');

            if (logoInput && logoContainer) {
                logoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            // Keep the input input but replace visual content
                            const existingInput = logoContainer.querySelector('input[type="file"]');
                            logoContainer.innerHTML = '';
                            logoContainer.appendChild(existingInput); // Re-append input to keep it functional
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'logo-preview-img';
                            logoContainer.appendChild(img);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });

        function toggleGstField(isRegistered) {
            const gstContainer = document.getElementById('gst-container');
            
            if (isRegistered) {
                gstContainer.style.display = 'block';
            } else {
                gstContainer.style.display = 'none';
                document.getElementById('gstin').value = ''; 
            }
        }

        function extractPanFromGst(gstin) {
            const panInput = document.getElementById('pan');
            // GSTIN format: 22AAAAA0000A1Z5 -> chars 3-12 (index 2-11) is PAN
            if (gstin.length >= 12) {
                const extractedPan = gstin.substring(2, 12).toUpperCase();
                // Simple regex check for PAN format (5 letters, 4 numbers, 1 letter)
                const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
                if (panRegex.test(extractedPan)) {
                    panInput.value = extractedPan;
                }
            }
        }
    </script>
</body>
</html>
