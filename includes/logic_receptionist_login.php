<?php
// ================= DEBUG =================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =========================================

require_once 'config/connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

global $pdo;
$error_message = '';
$RECEPTIONIST_TABLE = 'Receptionists';
$PASSWORD_COLUMN = 'hashed_pass';

// 1. KIỂM TRA SESSION: Nếu đã là Lễ tân rồi thì đá thẳng vào Dashboard
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Receptionist') {
    header("Location: receptionist_dashboard.php");
    exit;
}

// 2. XỬ LÝ KHI NHẤN NÚT ĐĂNG NHẬP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receptionist_login'])) {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Vui lòng nhập đầy đủ Email và Mật khẩu.';
    } else {
        try {
            // Kiểm tra Email trong bảng Lễ tân
            $sql_check = "SELECT receptionist_id, full_name, {$PASSWORD_COLUMN} FROM {$RECEPTIONIST_TABLE} WHERE email = :email";
            $stmt = $pdo->prepare($sql_check);
            $stmt->execute(['email' => $email]);
            $receptionist = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($receptionist) {
                // Kiểm tra mật khẩu
                if (password_verify($password, $receptionist[$PASSWORD_COLUMN])) {

                    // --- THIẾT LẬP SESSION ---
                    $_SESSION['receptionist_id'] = $receptionist['receptionist_id'];
                    $_SESSION['receptionist_name'] = $receptionist['full_name'];

                    // Quan trọng: Đặt Role để phân quyền
                    $_SESSION['user_role'] = 'Receptionist';

                    // --- CHUYỂN HƯỚNG VỀ DASHBOARD (Đã sửa chính xác tại đây) ---
                    header("Location: receptionist_dashboard.php");
                    exit;

                } else {
                    $error_message = 'Mật khẩu không chính xác.';
                }
            } else {
                $error_message = 'Email không tồn tại trong hệ thống Lễ tân.';
            }

        } catch (PDOException $e) {
            $error_message = 'Lỗi hệ thống database: ' . $e->getMessage();
        }
    }
}
?>