<?php
// Determine Protocol
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';

// Determine Environment
// Determine Environment
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    // Database Configuration - Local
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'garviterp');

    // Application Configuration - Local
    define('APP_NAME', 'Acculynce');
    define('APP_VERSION', '1.0.0');
    define('BASE_URL', $protocol . 'localhost/garvitrajput/');
    define('MODULES_URL', BASE_URL . 'modules');

} elseif ($_SERVER['HTTP_HOST'] === 'dev.acculynce.com') {
    // Database Configuration - Development
    // Using Prod DB credentials for now as placeholders, assuming shared or same server
    define('DB_HOST', 'localhost');
    define('DB_USER', 'eroiwmza_acculynce');
    define('DB_PASS', 'acculynce@246761');
    define('DB_NAME', 'eroiwmza_acculynce');

    // Application Configuration - Development
    define('APP_NAME', 'Acculynce');
    define('APP_VERSION', '1.0.0-dev');
    define('BASE_URL', $protocol . 'dev.acculynce.com/');
    define('MODULES_URL', BASE_URL . 'modules');

} else {
    // Database Configuration - Production (acculynce.com)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'eroiwmza_acculynce');
    define('DB_PASS', 'acculynce@246761');
    define('DB_NAME', 'eroiwmza_acculynce');

    // Application Configuration - Production
    define('APP_NAME', 'Acculynce');
    define('APP_VERSION', '1.0.0');
    define('BASE_URL', $protocol . 'acculynce.com/');
    define('MODULES_URL', BASE_URL . 'modules');
}

// Session Configuration
define('SESSION_LIFETIME', 86400); // 24 hours

ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

$cookieParams = [
    'lifetime' => SESSION_LIFETIME,
    'path' => '/',
    'domain' => '', // Default to current domain
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax'
];

session_set_cookie_params($cookieParams);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security
define('PASSWORD_SALT', 'tiger_erp_secure_salt_2025');
define('SESSION_NAME', 'TIGER_ERP_SESSION');

// Path Constants
define('ROOT_PATH', dirname(__DIR__));
define('MODULES_PATH', ROOT_PATH . '/modules');
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');

// Email Configuration (Resend)
define('SMTP_FROM_EMAIL', 'accounts@acculynce.com');
define('SMTP_FROM_NAME', 'Acculynce Accounts');
define('RESEND_API_KEY', 're_9JHbh382_56VYHuEmYz8wEKJWTrDLSKdL');

// Helper function to get module path
function module_path($module, $file = '') {
    return MODULES_PATH . '/' . $module . ($file ? '/' . $file : '');
}

// Obfuscated URL Helper
function url($path) {
    // If not in DB context or setup, return raw
    if (!class_exists('Database')) {
        return BASE_URL . $path;
    }
    
    // Lazy load Router
    static $router = null;
    if ($router === null) {
        if (file_exists(CLASSES_PATH . '/Router.php')) {
            require_once CLASSES_PATH . '/Router.php';
            $router = new Router();
        } else {
            return BASE_URL . $path;
        }
    }
    
    $token = $router->getToken($path);
    return BASE_URL . $token;
}

// -----------------------------------------------------------------------------
// CENTRAL ROUTE SECURITY ENFORCEMENT
// -----------------------------------------------------------------------------
// This "passes all routes through one file" logic by intercepting execution here.
// Only run if we are not in a CLI environment.
if (php_sapi_name() !== 'cli') {
    // Only enforce if the class file exists (setup phase protection)
    if (file_exists(CLASSES_PATH . '/Auth.php')) {
        require_once CLASSES_PATH . '/Auth.php';
        $auth = new Auth();
        $auth->enforceGlobalRouteSecurity();
    }
}