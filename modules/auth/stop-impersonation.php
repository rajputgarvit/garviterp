<?php
require_once '../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Check if impersonating
if (!isset($_SESSION['is_impersonating']) || !isset($_SESSION['original_user'])) {
    header('Location: ' . MODULES_URL . '/auth/login.php');
    exit;
}

// 2. Restore Original Session
$_SESSION['user_id'] = $_SESSION['original_user']['id'];
$_SESSION['role'] = $_SESSION['original_user']['role'];
$_SESSION['full_name'] = $_SESSION['original_user']['full_name'];
$_SESSION['email'] = $_SESSION['original_user']['email'];

// Remove company context of the impersonated user
unset($_SESSION['company_id']);
unset($_SESSION['is_impersonating']);
unset($_SESSION['original_user']);

// 3. Redirect back to Super Admin Dashboard (Companies list)
header('Location: ' . MODULES_URL . '/admin/companies.php');
exit;
