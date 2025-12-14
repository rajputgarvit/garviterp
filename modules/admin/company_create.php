<?php
$pageTitle = 'Add New Company';
$currentPage = 'companies';
require_once '../../config/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';

$db = Database::getInstance();
$auth = new Auth();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic Validation
    if (empty($_POST['company_name']) || empty($_POST['owner_name']) || empty($_POST['owner_email']) || empty($_POST['owner_password'])) {
        $error = 'Please fill in all required fields.';
    } else {
        // Data Preparation
        $companyName = trim($_POST['company_name']);
        $ownerName = trim($_POST['owner_name']);
        $ownerEmail = trim($_POST['owner_email']);
        $ownerPassword = $_POST['owner_password'];
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $gstin = trim($_POST['gstin'] ?? '');

        try {
            $db->beginTransaction();

            // 1. Create Company
            $db->query(
                "INSERT INTO company_settings (company_name, phone, address_line1, gstin) VALUES (?, ?, ?, ?)",
                [$companyName, $phone, $address, $gstin]
            );
            $companyId = $db->getConnection()->lastInsertId();

            // 2. Create Owner User
            $passwordHash = password_hash($ownerPassword, PASSWORD_DEFAULT);
            $db->query(
                "INSERT INTO users (company_id, username, password_hash, full_name, email, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())",
                [$companyId, $ownerEmail, $passwordHash, $ownerName, $ownerEmail] // Username is email
            );
            $userId = $db->getConnection()->lastInsertId();

            // 3. Assign Admin Role
            // Assuming role_id 1 is 'admin', verify this matches your system!
            // If strictly using the 'roles' table, fetch the ID for 'admin' first.
            $adminRole = $db->fetchOne("SELECT id FROM roles WHERE name = 'admin' OR name = 'company_admin' LIMIT 1");
            $roleId = $adminRole['id'] ?? 1; // Fallback to 1

            $db->query(
                "INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)",
                [$userId, $roleId]
            );

            $db->commit();
            $success = "Company '$companyName' created successfully with owner '$ownerName'.";
            
            // Redirect after short delay or show link
            header("refresh:2;url=companies.php");

        } catch (Exception $e) {
            $db->rollBack();
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                 $error = "Email address already exists.";
            } else {
                $error = "Error creating company: " . $e->getMessage();
            }
        }
    }
}

require_once '../../includes/admin_layout.php';
?>

<style>
    .company-create-wrapper {
        max-width: 900px;
        margin: 0 auto;
        padding: 2rem 0;
    }
    
    .page-header-section {
        margin-bottom: 2.5rem;
        text-align: center;
    }
    
    .page-header-section h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    
    .page-header-section p {
        color: var(--text-secondary);
        font-size: 0.95rem;
    }
    
    /* Progress Indicator */
    .progress-indicator {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 3rem;
        position: relative;
    }
    
    .progress-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        flex: 1;
        max-width: 200px;
    }
    
    .progress-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 20px;
        left: 50%;
        width: 100%;
        height: 2px;
        background: #e5e7eb;
        z-index: -1;
    }
    
    .progress-step.active:not(:last-child)::after,
    .progress-step.completed:not(:last-child)::after {
        background: var(--primary-color);
    }
    
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #9ca3af;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-bottom: 0.5rem;
        transition: all 0.3s;
    }
    
    .progress-step.active .step-circle {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
    }
    
    .progress-step.completed .step-circle {
        background: var(--primary-color);
        color: white;
    }
    
    .step-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }
    
    .progress-step.active .step-label {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    /* Form Card */
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    
    .form-card-header {
        background: linear-gradient(to right, #f9fafb, #ffffff);
        border-bottom: 1px solid #e5e7eb;
        padding: 1.25rem 1.75rem;
    }
    
    .form-card-header h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-card-header .icon {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
    }
    
    .form-card-body {
        padding: 1.75rem;
    }
    
    .form-field {
        margin-bottom: 1.5rem;
    }
    
    .form-field:last-child {
        margin-bottom: 0;
    }
    
    .form-field label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .form-field label .required {
        color: #ef4444;
        margin-left: 2px;
    }
    
    .form-field input,
    .form-field textarea {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1.5px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.9375rem;
        transition: all 0.15s ease;
        background: white;
    }
    
    .form-field input:focus,
    .form-field textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .form-field textarea {
        resize: vertical;
        min-height: 80px;
    }
    
    .form-field .help-text {
        display: block;
        font-size: 0.8125rem;
        color: #6b7280;
        margin-top: 0.375rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }
    
    /* Step Content */
    .step-content {
        display: none;
    }
    
    .step-content.active {
        display: block;
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Action Bar */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
        margin-top: 2rem;
    }
    
    .btn-primary-custom {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9375rem;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-primary-custom:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .btn-secondary-custom {
        background: white;
        color: #6b7280;
        border: 1.5px solid #d1d5db;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9375rem;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-secondary-custom:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
    
    .btn-cancel {
        color: #6b7280;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9375rem;
        transition: color 0.15s;
    }
    
    .btn-cancel:hover {
        color: var(--text-primary);
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .company-create-wrapper {
            padding: 1rem;
        }
        
        .progress-indicator {
            flex-direction: column;
            gap: 1rem;
        }
        
        .progress-step:not(:last-child)::after {
            display: none;
        }
    }
</style>

<div class="company-create-wrapper">
    
    <div class="page-header-section">
        <h1>Create New Company</h1>
        <p>Set up a new tenant account with administrator credentials</p>
    </div>

    <!-- Progress Indicator -->
    <div class="progress-indicator">
        <div class="progress-step active" data-step="1">
            <div class="step-circle">1</div>
            <div class="step-label">Company Info</div>
        </div>
        <div class="progress-step" data-step="2">
            <div class="step-circle">2</div>
            <div class="step-label">Administrator</div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="companyForm">
        
        <!-- Step 1: Company Information -->
        <div class="step-content active" data-step="1">
            <div class="form-card">
                <div class="form-card-header">
                    <h3>
                        <span class="icon"><i class="fas fa-building"></i></span>
                        Company Information
                    </h3>
                </div>
                <div class="form-card-body">
                    <div class="form-field">
                        <label>Company Legal Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            name="company_name" 
                            id="company_name"
                            required 
                            placeholder="e.g. Acme Industries Ltd." 
                            value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>"
                        >
                    </div>
                    
                    <div class="form-row">
                        <div class="form-field">
                            <label>Phone Number</label>
                            <input 
                                type="text" 
                                name="phone" 
                                id="phone"
                                placeholder="+91 98765 43210" 
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                            >
                        </div>
                        <div class="form-field">
                            <label>GSTIN / Tax ID</label>
                            <input 
                                type="text" 
                                name="gstin" 
                                id="gstin"
                                placeholder="22AAAAA0000A1Z5" 
                                value="<?php echo htmlspecialchars($_POST['gstin'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div class="form-field">
                        <label>Business Address</label>
                        <textarea 
                            name="address" 
                            id="address"
                            placeholder="Street address, city, state, postal code"
                        ><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="action-bar">
                <a href="companies.php" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="button" class="btn-primary-custom" onclick="nextStep()">
                    Next: Administrator
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- Step 2: Administrator Account -->
        <div class="step-content" data-step="2">
            <div class="form-card">
                <div class="form-card-header">
                    <h3>
                        <span class="icon"><i class="fas fa-user-shield"></i></span>
                        Administrator Account
                    </h3>
                </div>
                <div class="form-card-body">
                    <div class="form-field">
                        <label>Full Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            name="owner_name" 
                            id="owner_name"
                            required 
                            placeholder="John Doe" 
                            value="<?php echo htmlspecialchars($_POST['owner_name'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-field">
                        <label>Email Address <span class="required">*</span></label>
                        <input 
                            type="email" 
                            name="owner_email" 
                            id="owner_email"
                            required 
                            placeholder="admin@company.com" 
                            value="<?php echo htmlspecialchars($_POST['owner_email'] ?? ''); ?>"
                        >
                        <span class="help-text">This will be used as the login username</span>
                    </div>

                    <div class="form-field">
                        <label>Password <span class="required">*</span></label>
                        <input 
                            type="password" 
                            name="owner_password" 
                            id="owner_password"
                            required 
                            minlength="6" 
                            placeholder="Enter a secure password"
                        >
                        <span class="help-text">Minimum 6 characters required</span>
                    </div>
                </div>
            </div>

            <div class="action-bar">
                <button type="button" class="btn-secondary-custom" onclick="prevStep()">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </button>
                <button type="submit" class="btn-primary-custom">
                    <i class="fas fa-check"></i>
                    Create Company
                </button>
            </div>
        </div>

    </form>
</div>

<script>
let currentStep = 1;

function updateProgress() {
    // Update progress steps
    document.querySelectorAll('.progress-step').forEach(step => {
        const stepNum = parseInt(step.dataset.step);
        step.classList.remove('active', 'completed');
        
        if (stepNum === currentStep) {
            step.classList.add('active');
        } else if (stepNum < currentStep) {
            step.classList.add('completed');
        }
    });
    
    // Update step content
    document.querySelectorAll('.step-content').forEach(content => {
        content.classList.remove('active');
    });
    document.querySelector(`.step-content[data-step="${currentStep}"]`).classList.add('active');
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextStep() {
    // Validate current step
    const currentStepElement = document.querySelector(`.step-content[data-step="${currentStep}"]`);
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.focus();
            field.style.borderColor = '#ef4444';
            setTimeout(() => {
                field.style.borderColor = '';
            }, 2000);
        }
    });
    
    if (!isValid) {
        return;
    }
    
    if (currentStep < 2) {
        currentStep++;
        updateProgress();
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateProgress();
    }
}

// Initialize
updateProgress();
</script>

</div> <!-- End content-area -->
</main>
</div> <!-- End dashboard-wrapper -->
</body>
</html>
