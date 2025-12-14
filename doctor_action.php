<?php
// FILE: doctor_action.php
require_once 'config/connect.php';

if (isset($_GET['action']) && $_GET['action'] == 'call' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Cập nhật trạng thái thành Đang khám
    $stmt = $pdo->prepare("UPDATE Appointments SET status = 'Examining' WHERE appointment_id = :id");
    $stmt->execute(['id' => $id]);

    // Quay lại dashboard bác sĩ
    header("Location: doctor_dashboard.php");
    exit;
}
?>