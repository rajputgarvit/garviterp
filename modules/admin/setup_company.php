<?php
$pageTitle = 'Setup Your Company';
require_once '../../config/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();

// if (!$auth->hasRole('Super Admin')) {
//     header('Location: ' . MODULES_URL . '/dashboard/index.php');
//     exit;
// }

$db = Database::getInstance();
$user = $auth->getCurrentUser();

// If user already has a valid company, redirect to dashboard
if (!empty($user['company_id'])) {
    $existingCompany = $db->fetchOne("SELECT id FROM company_settings WHERE id = ?", [$user['company_id']]);
    if ($existingCompany) {
        header('Location: ' . MODULES_URL . '/dashboard/index.php');
        exit;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = trim($_POST['company_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gstin = trim($_POST['gstin'] ?? '');
    
    if (empty($companyName)) {
        $error = 'Company Name is required.';
    } else {
        try {
            $db->beginTransaction();
            
            // Create Company
            $db->query(
                "INSERT INTO company_settings (company_name, address_line1, phone, gstin) VALUES (?, ?, ?, ?)",
                [$companyName, $address, $phone, $gstin]
            );
            $companyId = $db->getConnection()->lastInsertId();
            
            // Update User
            $db->query("UPDATE users SET company_id = ? WHERE id = ?", [$companyId, $user['id']]);
            
            // Update Session
            $_SESSION['company_id'] = $companyId;
            
            $db->commit();
            
            $success = 'Company setup successfully! Redirecting...';
            header("refresh:2;url=" . MODULES_URL . "/dashboard/index.php");
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Error setting up company: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Company - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        .setup-card {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 500px;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .setup-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        .setup-header p {
            color: #6b7280;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }
        .btn-primary {
            width: 100%;
            padding: 0.875rem;
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #4338ca;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="setup-header">
            <h1>Setup Your Company</h1>
            <p>Please provide your company details to get started.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label>Company Name <span style="color: red;">*</span></label>
                    <input type="text" name="company_name" class="form-control" required placeholder="e.g. Acme Corp">
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210">
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Registered office address"></textarea>
                </div>
                
                <div class="form-group">
                    <label>GSTIN (Optional)</label>
                    <input type="text" name="gstin" class="form-control" placeholder="GST Number">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-building"></i> Create Company & Continue
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
