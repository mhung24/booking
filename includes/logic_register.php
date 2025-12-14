<?php
require_once 'config/connect.php';
session_start();

$error_message = '';
$success_message = '';

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $phone_number = trim($_POST['phone_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($full_name) || empty($phone_number) || empty($password) || empty($confirm_password)) {
        $error_message = "Vui lòng điền đầy đủ Họ tên, Số điện thoại và Mật khẩu.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Xác nhận mật khẩu không khớp.";
    } elseif (strlen($password) < 6) {
        $error_message = "Mật khẩu phải chứa ít nhất 6 ký tự.";
    } else {
        global $pdo;

        $sql_check = "SELECT patient_id FROM Patients WHERE phone_number = :phone_number";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([':phone_number' => $phone_number]);

        if ($stmt_check->fetch()) {
            $error_message = "Số điện thoại này đã được sử dụng. Vui lòng chọn số khác hoặc Đăng nhập.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql_insert = "
                INSERT INTO Patients (full_name, phone_number, password_hash, email) 
                VALUES (:full_name, :phone_number, :password_hash, :email)
            ";

            try {
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    ':full_name' => $full_name,
                    ':phone_number' => $phone_number,
                    ':password_hash' => $password_hash,
                    ':email' => $_POST['email'] ?? null
                ]);

                $success_message = "Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.";

            } catch (PDOException $e) {
                $error_message = "Lỗi hệ thống khi đăng ký: " . $e->getMessage();
            }
        }
    }
}
?>