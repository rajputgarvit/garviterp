<?php
// Determine Protocol
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';

// Determine Environment
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    // Database Configuration - Local
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'garviterp');

    // Application Configuration - Local
    define('APP_NAME', 'Hawk ERP');
    define('APP_VERSION', '1.0.0');
    define('BASE_URL', $protocol . 'localhost/garvitrajput/');
    define('MODULES_URL', BASE_URL . 'modules');
} else {
    // Database Configuration - Prod
    define('DB_HOST', 'sdb-68.hosting.stackcp.net');
    define('DB_USER', 'garviterp-353034391dd2');
    define('DB_PASS', 'garviterp353034391dd2');
    define('DB_NAME', 'garviterp-353034391dd2');

    // Application Configuration - Prod
    define('APP_NAME', 'Hawk ERP');
    define('APP_VERSION', '1.0.0');
    define('BASE_URL', $protocol . 'garvitrajput.co.in/');
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

// Helper function to get module path
function module_path($module, $file = '') {
    return MODULES_PATH . '/' . $module . ($file ? '/' . $file : '');
}