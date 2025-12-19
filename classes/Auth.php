<?php
require_once __DIR__ . '/Database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login($username, $password) {
        $user = $this->db->fetchOne(
            "SELECT u.*, GROUP_CONCAT(r.name) as roles 
             FROM users u 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1 
             GROUP BY u.id",
            [$username, $username]
        );
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $this->db->update('users', 
                ['last_login' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$user['id']]
            );
            
            // Set session
            $this->setSession($user);
            
            // Log audit
            $this->logAudit($user['id'], 'login', 'users', $user['id']);
            
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            try {
                $this->logAudit($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id']);
            } catch (Exception $e) {
                // Ignore audit error during logout if user doesn't exist
            }
        }
        
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            // If company_id is missing in session (stale session), refresh it from DB
            if (!isset($_SESSION['company_id'])) {
                $user = $this->db->fetchOne("SELECT company_id FROM users WHERE id = ?", [$_SESSION['user_id']]);
                if ($user) {
                    $_SESSION['company_id'] = $user['company_id'];
                }
            }

            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'email' => $_SESSION['email'],
                'company_id' => $_SESSION['company_id'] ?? null,
                'roles' => $_SESSION['roles'] ?? []
            ];
        }
        return null;
    }
    
    public function hasRole($role) {
        if (!$this->isLoggedIn()) return false;
        
        $roles = $_SESSION['roles'] ?? [];
        if (is_string($roles)) {
            $roles = explode(',', $roles);
        }
        
        return in_array($role, $roles) || in_array('Super Admin', $roles);
    }

    public function isAdmin() {
        return $this->hasRole('Super Admin');
    }
    
    public function hasPermission($module, $action) {
        if (!$this->isLoggedIn()) return false;
        
        // Super Admin has all permissions
        if ($this->hasRole('Super Admin')) return true;
        
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count 
             FROM user_roles ur
             JOIN role_permissions rp ON ur.role_id = rp.role_id
             JOIN permissions p ON rp.permission_id = p.id
             WHERE ur.user_id = ? AND p.module = ? AND p.action = ?",
            [$_SESSION['user_id'], $module, $action]
        );
        
        return $result['count'] > 0;
    }

    public function hasModuleAccess($module) {
        if (!$this->isLoggedIn()) return false;

        // Super Admin has access to everything
        if ($this->hasRole('Super Admin')) return true;

        // Company Admin (Role ID 2) has access to everything
        // We can check role name 'Admin' or ID 2. Let's check role name for clarity if possible, 
        // but roles are stored as comma separated string in session or we can query.
        // For now, let's assume if they have 'Admin' role they see everything.
        if ($this->hasRole('Admin')) return true;

        // Check specific module access
        $access = $this->db->fetchOne(
            "SELECT id FROM user_module_access WHERE user_id = ? AND module = ?",
            [$_SESSION['user_id'], $module]
        );

        return $access ? true : false;
    }
    
    private function setSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['company_id'] = $user['company_id'];
        $_SESSION['roles'] = $user['roles'];
    }
    
    public function register($data) {
        // Check if username or email exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM users WHERE username = ? OR email = ?",
            [$data['username'], $data['email']]
        );
        
        if ($existing) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Create new company (using company_settings table)
        $companyId = $this->db->insert('company_settings', [
            'company_name' => $data['company_name'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if (!$companyId) {
            return ['success' => false, 'message' => 'Failed to create company'];
        }

        // Hash password
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        
        // Add company_id to user data
        $data['company_id'] = $companyId;
        
        // Insert user
        $userId = $this->db->insert('users', $data);
        
        // Assign default role (Admin for the new company creator)
        // Assuming role_id 2 is Admin (need to verify, but usually 1=Super Admin, 2=Admin, 4=Employee)
        // Let's use 2 (Admin) for the company creator
        $this->db->insert('user_roles', [
            'user_id' => $userId,
            'role_id' => 2 // Admin role
        ]);
        
        return ['success' => true, 'user_id' => $userId];
    }
    
    public function changePassword($userId, $oldPassword, $newPassword) {
        $user = $this->db->fetchOne("SELECT password_hash FROM users WHERE id = ?", [$userId]);
        
        if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        $this->db->update('users', 
            ['password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)],
            'id = ?',
            [$userId]
        );
        
        $this->logAudit($userId, 'password_change', 'users', $userId);
        
        return ['success' => true, 'message' => 'Password changed successfully'];
    }
    
    private function logAudit($userId, $action, $table = null, $recordId = null, $oldValues = null, $newValues = null) {
        $this->db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . BASE_URL . 'modules/auth/login.php');
            exit;
        }
    }

    /**
     * Guards access to a specific module and action.
     * If the user does not have permission, it redirects them.
     */
    public function guard($module, $action) {
        if (!$this->hasPermission($module, $action)) {
            // Standard redirect
            $redirect = defined('BASE_URL') ? BASE_URL : '/garvitrajput/';
            header('Location: ' . $redirect . 'modules/dashboard/index.php?error=Access denied: ' . ucfirst($module) . ' ' . ucfirst($action));
            exit;
        }
    }

    /**
     * Checks current URL against central route map and enforces permissions automatically.
     */
    public function enforceGlobalRouteSecurity() {
        if (php_sapi_name() === 'cli') return; // detailed verify

        $routes = require __DIR__ . '/../config/route_permissions.php';
        
        // Get relative path: /garvitrajput/modules/sales/index.php -> modules/sales/index.php
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = parse_url(BASE_URL, PHP_URL_PATH);
        if ($basePath && strpos($scriptName, $basePath) === 0) {
            $relativePath = substr($scriptName, strlen($basePath));
        } else {
            $relativePath = ltrim($scriptName, '/');
        }
        $relativePath = ltrim($relativePath, '/'); // Ensure no leading slash

        foreach ($routes as $pattern => $perms) {
            if (preg_match($pattern, $relativePath)) {
                // Determine if we need to check login first
                // If it's a secured route, we MUST be logged in
                if (!$this->isLoggedIn()) {
                    $this->requireLogin();
                }
                
                $this->guard($perms[0], $perms[1]);
                break; // Stop after first match
            }
        }
    }
    
    /**
     * Send email verification
     */
    public function sendVerificationEmail($userId, $email) {
        // Generate verification token
        $token = bin2hex(random_bytes(32));
        
        // Update user with token
        $this->db->update('users',
            ['email_verification_token' => $token],
            'id = ?',
            [$userId]
        );
        
        // Send email (simplified version - should use proper email service)
    // Send email 
    $verificationLink = BASE_URL . "modules/auth/verify-email.php?token=" . $token;
    $subject = "Verify your Acculynce account";
    $logoUrl = "https://dev.acculynce.com/public/uploads/logos/logo_1_1765731868.svg";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Verify Email</title>
        <style>
            body { font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #1f2937; line-height: 1.6; margin: 0; padding: 0; background-color: #f3f4f6; }
            .wrapper { width: 100%; background-color: #f3f4f6; padding: 40px 0; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
            .header { background-color: #111827; padding: 30px; text-align: center; }
            .header img { height: 40px; width: auto; }
            .content { padding: 40px 30px; }
            .h1 { font-size: 24px; font-weight: 700; margin-top: 0; margin-bottom: 16px; color: #111827; }
            .text { font-size: 16px; color: #4b5563; margin-bottom: 24px; }
            .btn-container { text-align: center; margin: 32px 0; }
            .btn { display: inline-block; padding: 14px 32px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; transition: background-color 0.2s; }
            .btn:hover { background-color: #1d4ed8; }
            .footer { background-color: #f9fafb; padding: 24px; text-align: center; border-top: 1px solid #e5e7eb; }
            .footer-text { font-size: 12px; color: #6b7280; margin-bottom: 8px; }
            .link { color: #2563eb; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='wrapper'>
            <div class='container'>
                <div class='header'>
                    <img src='$logoUrl' alt='Acculynce Logo'>
                </div>
                <div class='content'>
                    <h1 class='h1'>Verify your email address</h1>
                    <p class='text'>Thanks for signing up for " . APP_NAME . "! We're excited to have you on board.</p>
                    <p class='text'>Please verify your email address to get access to all features. Just click the button below:</p>
                    
                    <div class='btn-container'>
                        <a href='$verificationLink' class='btn'>Verify Email Address</a>
                    </div>
                    
                    <p class='text' style='margin-bottom: 0; font-size: 14px; color: #6b7280;'>
                        If you didn't create an account, you can safely ignore this email. The link will expire in 24 hours.
                    </p>
                </div>
                <div class='footer'>
                    <p class='footer-text'>&copy; " . date('Y') . " " . APP_NAME . ". All rights reserved.</p>
                    <p class='footer-text'>
                        <a href='" . BASE_URL . "' class='link'>Visit Website</a>
                    </p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
        
        // Use Mail class
        require_once __DIR__ . '/Mail.php';
        $mail = new Mail();
        return $mail->sendWithResend($email, $subject, $message);
    }
    
    /**
     * Verify email token
     */
    public function verifyEmail($token) {
        $user = $this->db->fetchOne(
            "SELECT id FROM users WHERE email_verification_token = ?",
            [$token]
        );
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid verification token'];
        }
        
        $this->db->update('users',
            [
                'email_verified' => 1,
                'email_verification_token' => null
            ],
            'id = ?',
            [$user['id']]
        );
        
        return ['success' => true, 'user_id' => $user['id']];
    }
    
    /**
     * Check if user has active subscription
     */
    public function checkSubscriptionAccess() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $user = $this->getCurrentUser();
        $companyId = $user['company_id'];

        if (!$companyId) {
            return false; // Should not happen for valid users
        }
        
        require_once 'Subscription.php';
        $subscription = new Subscription();
        return $subscription->hasActiveSubscription($companyId);
    }
    /**
     * Impersonate a user (Admin only)
     */
    public function impersonateUser($userId) {
        if (!$this->isAdmin()) {
            return false;
        }

        $targetUser = $this->db->fetchOne(
            "SELECT u.*, GROUP_CONCAT(r.name) as roles 
             FROM users u 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE u.id = ?
             GROUP BY u.id", 
            [$userId]
        );

        if (!$targetUser) {
            return false;
        }

        // Save original admin session
        $_SESSION['admin_user_id'] = $_SESSION['user_id'];
        $_SESSION['admin_username'] = $_SESSION['username'];
        $_SESSION['admin_full_name'] = $_SESSION['full_name'];
        $_SESSION['admin_roles'] = $_SESSION['roles'];
        $_SESSION['is_impersonating'] = true;

        // Log audit
        $this->logAudit($_SESSION['user_id'], 'impersonate_start', 'users', $userId);

        // Set session to target user
        $this->setSession($targetUser);
        
        return true;
    }

    /**
     * Stop impersonation and return to admin
     */
    public function stopImpersonation() {
        if (!isset($_SESSION['is_impersonating']) || !$_SESSION['is_impersonating']) {
            return false;
        }

        $adminId = $_SESSION['admin_user_id'];

        // Log audit
        $this->logAudit($adminId, 'impersonate_end', 'users', $_SESSION['user_id']);

        // Restore admin session
        $_SESSION['user_id'] = $_SESSION['admin_user_id'];
        $_SESSION['username'] = $_SESSION['admin_username'];
        $_SESSION['full_name'] = $_SESSION['admin_full_name'];
        $_SESSION['roles'] = $_SESSION['admin_roles'];
        
        // Clear impersonation flags
        unset($_SESSION['admin_user_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_full_name']);
        unset($_SESSION['admin_roles']);
        unset($_SESSION['is_impersonating']);
        unset($_SESSION['company_id']); // Will be reset on next admin action or not needed for global admin

        return true;
    }

    public function isImpersonating() {
        return isset($_SESSION['is_impersonating']) && $_SESSION['is_impersonating'];
    }

    /**
     * Initiate Password Reset
     */
    public function initiatePasswordReset($email) {
        $user = $this->db->fetchOne("SELECT id, username FROM users WHERE email = ? AND is_active = 1", [$email]);
        
        if (!$user) {
            return false;
        }

        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->db->update('users', 
            [
                'password_reset_token' => $token,
                'password_reset_expires_at' => $expiresAt
            ],
            'id = ?',
            [$user['id']]
        );

        $resetLink = BASE_URL . "modules/auth/reset-password.php?token=" . $token;
        $subject = "Reset your password";
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Password Reset Request</h2>
                <p>Hello " . htmlspecialchars($user['username']) . ",</p>
                <p>We received a request to reset your password. Click the button below to choose a new password:</p>
                <p><a href='$resetLink' class='btn'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, user simply ignore this email.</p>
            </div>
        </body>
        </html>
        ";

        require_once __DIR__ . '/Mail.php';
        $mail = new Mail();
        // Use sendWithResend or similar depending on Mail implementation
        return $mail->sendWithResend($email, $subject, $message);
    }

    /**
     * Verify Password Reset Token
     */
    public function verifyPasswordResetToken($token) {
        if (empty($token)) return false;

        $user = $this->db->fetchOne(
            "SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires_at > NOW()",
            [$token]
        );

        return $user ? $user['id'] : false;
    }

    /**
     * Reset Password with Token
     */
    public function resetPasswordWithToken($token, $newPassword) {
        $userId = $this->verifyPasswordResetToken($token);
        
        if (!$userId) {
            return false;
        }

        $this->db->update('users',
            [
                'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'password_reset_token' => null,
                'password_reset_expires_at' => null
            ],
            'id = ?',
            [$userId]
        );
        
        // Log password change
        $this->logAudit($userId, 'password_reset_via_token', 'users', $userId);

        return true;
    }
}

