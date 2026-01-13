<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/connect.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

$allowed_roles = ['Admin', 'HR_Admin', 'Super'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: login.php");
    exit;
}

global $pdo;

try {
    $sql_stats = "
        SELECT 
            (SELECT COUNT(*) FROM Doctors) as count_doctors,
            (SELECT COUNT(*) FROM Receptionists) as count_receptionists,
            (SELECT COUNT(*) FROM Admins) as count_admins,
            (SELECT COUNT(*) FROM Departments) as count_departments
    ";
    $stmt = $pdo->query($sql_stats);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $stats = [
        'count_doctors' => 0,
        'count_receptionists' => 0,
        'count_admins' => 0,
        'count_departments' => 0
    ];
}

try {
    $sql_recent = "
        (SELECT 'Doctor' as role, full_name, created_at FROM Doctors)
        UNION ALL
        (SELECT 'Receptionist' as role, full_name, created_at FROM Receptionists)
        ORDER BY created_at DESC 
        LIMIT 5
    ";
    $recent_list = $pdo->query($sql_recent)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_list = [];
}
?>