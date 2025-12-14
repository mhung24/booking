<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$allowed_roles = ['Super', 'HR_Admin'];

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    header('Location: login.php');
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'Quản lý';
$admin_role = $_SESSION['user_role'] ?? 'Admin';
?>