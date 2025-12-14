<?php
// session_start(); // Handled in config.php
require_once 'config/config.php';
require_once 'classes/Auth.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    if ($auth->isAdmin()) {
        header('Location: ' . MODULES_URL . '/admin/dashboard.php');
    } else {
        header('Location: ' . MODULES_URL . '/dashboard/index.php');
    }
} else {
    header('Location: ' . MODULES_URL . '/public/landing.html');
}
exit;
