<?php
// FILE: includes/logic_create_receptionist.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/connect.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

// 1. CHECK QUYỀN (Admin/HR/Super mới được tạo)
$allowed_roles = ['Admin', 'HR_Admin', 'Super'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    die('<div class="alert alert-danger text-center m-5"><h1>⛔ TRUY CẬP BỊ TỪ CHỐI</h1><p>Bạn không có quyền.</p><a href="login.php">Đăng nhập lại</a></div>');
}

global $pdo;
$message = '';
$error_message = '';

// 2. XỬ LÝ KHI SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_account'])) {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    // --- THAY ĐỔI: MẬT KHẨU MẶC ĐỊNH LÀ 'admin' ---
    $default_password = 'admin';

    if (empty($full_name) || empty($email) || empty($phone_number)) {
        $error_message = 'Vui lòng điền đầy đủ thông tin.';
    } else {
        try {
            // Check trùng Email hoặc SĐT
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Receptionists WHERE email = :email OR phone_number = :phone");
            $stmt_check->execute(['email' => $email, 'phone' => $phone_number]);

            if ($stmt_check->fetchColumn() > 0) {
                $error_message = 'Email hoặc Số điện thoại đã tồn tại trong hệ thống.';
            } else {
                // Mã hóa mật khẩu 'admin'
                $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

                // Insert vào DB (Giả sử bảng tên là Receptionists)
                // Lưu ý: Cột mật khẩu mình đang để là 'password_hash' cho đồng bộ với bảng Doctors.
                // Nếu bảng này bạn dùng 'password' thì sửa lại nhé.
                $sql_insert = "
                    INSERT INTO Receptionists (full_name, email, phone_number, password_hash, created_at) 
                    VALUES (:full_name, :email, :phone, :pass, NOW())
                ";

                $stmt = $pdo->prepare($sql_insert);
                $stmt->execute([
                    'full_name' => $full_name,
                    'email' => $email,
                    'phone' => $phone_number,
                    'pass' => $hashed_password
                ]);

                // Thông báo & Chuyển hướng
                $_SESSION['success_message'] = "Đã tạo Lễ tân <strong>$full_name</strong> thành công! <br> Mật khẩu mặc định: <strong>admin</strong>";
                header("Location: hr_personnel_management.php");
                exit;
            }
        } catch (PDOException $e) {
            $error_message = 'Lỗi Database: ' . $e->getMessage();
        }
    }
}
?>