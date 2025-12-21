<?php
$pageTitle = 'My Profile';
$currentPage = 'settings';
require_once '../../config/config.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Database.php';

$auth = new Auth();
$auth->requireLogin();
$db = Database::getInstance();
$user = $auth->getCurrentUser();

$success = '';
$error = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $user['id'];
    
    // 1. Update Basic Info
    if (isset($_POST['update_profile'])) {
        $fullName = trim($_POST['full_name']);
        
        if (empty($fullName)) {
            $error = "Full Name is required.";
        } else {
            // Handle Avatar Upload
            $avatarPath = $user['avatar_path'] ?? null; // Default to existing or null
            
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../public/uploads/avatars/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileInfo = pathinfo($_FILES['avatar']['name']);
                $ext = strtolower($fileInfo['extension']);
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($ext, $allowed)) {
                    $newFilename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                    $targetPath = $uploadDir . $newFilename;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                        $avatarPath = 'public/uploads/avatars/' . $newFilename;
                    } else {
                        $error = "Failed to upload avatar.";
                    }
                } else {
                    $error = "Invalid file type. Only JPG, PNG, and WebP are allowed.";
                }
            }
            
            if (empty($error)) {
                try {
                    $db->update('users', [
                        'full_name' => $fullName,
                        'avatar_path' => $avatarPath
                    ], 'id = ?', [$userId]);
                    
                    // Refresh user session data
                    $user['full_name'] = $fullName;
                    $user['avatar_path'] = $avatarPath;
                    
                    // Update global session variables used by Auth class
                    $_SESSION['full_name'] = $fullName;
                    $_SESSION['avatar_path'] = $avatarPath;
                    
                    $success = "Profile updated successfully.";
                } catch (Exception $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            }
        }
    }
    
    // 2. Update Password
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = "All password fields are required.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match.";
        } else {
            // Verify current password
            $dbUser = $db->fetchOne("SELECT password_hash FROM users WHERE id = ?", [$userId]);
            
            if (password_verify($currentPassword, $dbUser['password_hash'])) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $db->update('users', ['password_hash' => $newHash], 'id = ?', [$userId]);
                $success = "Password changed successfully.";
            } else {
                $error = "Incorrect current password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles to match company.php aesthetic */
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            margin-bottom: 24px;
        }
        
        .card-header-custom {
            padding: 20px 24px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title-text {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
        }
        
        .card-body-custom {
            padding: 24px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            font-size: 0.95rem;
            line-height: 1.5;
            color: #1f2937;
            background-color: #fff;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            outline: 0;
            border-color: #7c3aed; /* Primary Purple */
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        
        /* Avatar Upload */
        .avatar-upload-container {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 24px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px dashed #d1d5db;
        }
        
        .current-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background-color: #e5e7eb;
        }
        
        .avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #7c3aed;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 600;
        }
        
        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .btn-upload {
            border: 1px solid #d1d5db;
            color: #374151;
            background-color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-upload:hover {
            background-color: #f3f4f6;
            border-color: #9ca3af;
        }
        
        .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }
        
        .helper-text {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .btn-primary {
            background-color: #7c3aed;
            border-color: #7c3aed;
            color: white;
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 6px;
        }
        
        .btn-primary:hover {
            background-color: #6d28d9;
            border-color: #6d28d9;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include INCLUDES_PATH . '/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include INCLUDES_PATH . '/header.php'; ?>
            
            <div class="content-area">
                <div class="settings-container">
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" style="margin-bottom: 20px; border-radius: 6px;">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="margin-bottom: 20px; border-radius: 6px;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Info Card -->
                    <div class="profile-card">
                        <div class="card-header-custom">
                            <span class="card-title-text"><i class="fas fa-user-circle" style="color: #7c3aed; margin-right: 8px;"></i> Personal Information</span>
                        </div>
                        <div class="card-body-custom">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="update_profile" value="1">
                                
                                <div class="avatar-upload-container">
                                    <?php if (!empty($user['avatar_path'])): ?>
                                        <img src="<?php echo BASE_URL . $user['avatar_path']; ?>" alt="Avatar" class="current-avatar" id="avatarPreview">
                                    <?php else: ?>
                                        <div class="avatar-placeholder" id="avatarPlaceholder">
                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                        </div>
                                        <img src="" alt="Preview" class="current-avatar" id="avatarPreview" style="display: none;">
                                    <?php endif; ?>
                                    
                                    <div>
                                        <h4 style="margin: 0 0 4px 0; font-size: 1rem; color: #111827;">Profile Photo</h4>
                                        <p class="helper-text" style="margin-bottom: 12px;">Accepts JPG, PNG or WebP (Max 2MB)</p>
                                        <div class="upload-btn-wrapper">
                                            <button type="button" class="btn-upload"><i class="fas fa-camera"></i> Change Photo</button>
                                            <input type="file" name="avatar" id="avatarInput" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                    <div>
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background-color: #f3f4f6; cursor: not-allowed;">
                                        <p class="helper-text">Email cannot be changed directly.</p>
                                    </div>
                                </div>
                                
                                <div style="text-align: right;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Password Change Card -->
                    <div class="profile-card">
                        <div class="card-header-custom">
                            <span class="card-title-text"><i class="fas fa-lock" style="color: #6b7280; margin-right: 8px;"></i> Change Password</span>
                        </div>
                        <div class="card-body-custom">
                            <form method="POST">
                                <input type="hidden" name="change_password" value="1">
                                
                                <div style="margin-bottom: 16px;">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required placeholder="Enter your current password">
                                </div>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                    <div>
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control" required placeholder="Min 8 characters">
                                    </div>
                                    <div>
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control" required placeholder="Re-enter new password">
                                    </div>
                                </div>
                                
                                <div style="text-align: right;">
                                    <button type="submit" class="btn btn-primary" style="background-color: #4b5563; border-color: #4b5563;">
                                        <i class="fas fa-key"></i> Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Avatar Preview Logic
        document.getElementById('avatarInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatarPreview');
                    const placeholder = document.getElementById('avatarPlaceholder');
                    
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
