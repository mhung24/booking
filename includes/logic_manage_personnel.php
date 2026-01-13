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
$success_msg = '';
$error_msg = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_personnel'])) {
    $id = $_POST['id'];
    $role = $_POST['role'];

    try {
        if ($role == 'Doctor') {
            $sql = "DELETE FROM Doctors WHERE doctor_id = ?";
        } elseif ($role == 'Receptionist') {
            $sql = "DELETE FROM Receptionists WHERE receptionist_id = ?";
        } elseif ($role == 'Admin') {
            $sql = "DELETE FROM Admins WHERE admin_id = ?";
        }

        if (isset($sql)) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $_SESSION['success_message'] = "Đã xóa nhân sự thành công!";
            header("Location: manage_personnel.php");
            exit;
        }
    } catch (PDOException $e) {
        $error_msg = "Lỗi khi xóa: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_personnel'])) {
    $id = $_POST['edit_id'];
    $role = $_POST['edit_role'];
    $name = trim($_POST['edit_name']);
    $email = trim($_POST['edit_email']);
    $phone = trim($_POST['edit_phone']);

    try {
        if ($role == 'Doctor') {
            $sql = "UPDATE Doctors SET full_name = ?, email = ?, phone_number = ? WHERE doctor_id = ?";
        } elseif ($role == 'Receptionist') {
            $sql = "UPDATE Receptionists SET full_name = ?, email = ?, phone_number = ? WHERE receptionist_id = ?";
        } elseif ($role == 'Admin') {
            $sql = "UPDATE Admins SET full_name = ?, email = ?, phone_number = ? WHERE admin_id = ?";
        }

        if (isset($sql)) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $phone, $id]);
            $_SESSION['success_message'] = "Cập nhật thông tin thành công!";
            header("Location: manage_personnel.php");
            exit;
        }
    } catch (PDOException $e) {
        $error_msg = "Lỗi cập nhật: " . $e->getMessage();
    }
}


$personnel_list = [];
$search_keyword = trim($_GET['search'] ?? '');
$term = "%$search_keyword%";

try {

    $sql = "
        SELECT 'Doctor' as role_type, doctor_id as id, full_name, email, phone_number, license_code as code, 'Bác sĩ' as role_display 
        FROM Doctors WHERE full_name LIKE :s1 OR phone_number LIKE :s2
        UNION ALL
        SELECT 'Receptionist' as role_type, receptionist_id as id, full_name, email, phone_number, 'N/A' as code, 'Lễ tân' as role_display 
        FROM Receptionists WHERE full_name LIKE :s3 OR phone_number LIKE :s4
        UNION ALL
        SELECT 'Admin' as role_type, admin_id as id, full_name, email, phone_number, 'ADMIN' as code, admin_role as role_display 
        FROM Admins WHERE full_name LIKE :s5 OR phone_number LIKE :s6
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['s1' => $term, 's2' => $term, 's3' => $term, 's4' => $term, 's5' => $term, 's6' => $term]);
    $personnel_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Lấy thông báo từ Session (nếu có)
if (isset($_SESSION['success_message'])) {
    $success_msg = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>