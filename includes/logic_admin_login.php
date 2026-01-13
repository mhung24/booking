<?php
// FILE: includes/logic_admin_login.php

// --- SỬA LỖI SESSION TẠI ĐÂY ---
// Chỉ start session nếu chưa có session nào đang chạy
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/connect.php';

$error_message = '';
$ADMIN_TABLE = 'Admins';
// Lưu ý: Kiểm tra kỹ tên cột mật khẩu trong bảng Admins của bạn (hashed_pass hay password_hash)
$PASSWORD_COLUMN = 'hashed_pass';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {

    // Lấy Email thay vì Username
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Vui lòng nhập đầy đủ Email và Mật khẩu.';
    } else {
        try {
            // Check theo cột email
            $sql_check = "SELECT admin_id, full_name, {$PASSWORD_COLUMN}, admin_role 
                          FROM {$ADMIN_TABLE} 
                          WHERE email = :email";

            $stmt = $pdo->prepare($sql_check);
            $stmt->execute(['email' => $email]);
            $admin_account = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin_account) {
                // Lấy mật khẩu hash từ DB
                $hashed_password_from_db = $admin_account[$PASSWORD_COLUMN];

                // So khớp mật khẩu
                if (password_verify($password, $hashed_password_from_db)) {

                    // Login thành công
                    if (session_status() === PHP_SESSION_NONE)
                        session_start();

                    $_SESSION['user_id'] = $admin_account['admin_id'];
                    $_SESSION['user_role'] = $admin_account['admin_role']; // Super, HR_Admin...
                    $_SESSION['full_name'] = $admin_account['full_name'];

                    // Chuyển hướng
                    $role = $admin_account['admin_role'];
                    if ($role === 'Super') {
                        header("Location: admin_dashboard.php");
                    } elseif ($role === 'HR_Admin') {
                        header("Location: hr_personnel_management.php");
                    } else {
                        header("Location: admin_dashboard.php");
                    }
                    exit;

                } else {
                    $error_message = 'Mật khẩu không chính xác.';
                }
            } else {
                $error_message = 'Email quản trị không tồn tại.';
            }

        } catch (PDOException $e) {
            $error_message = 'Lỗi hệ thống database: ' . $e->getMessage();
        }
    }
}
?>