<?php
require_once 'config/connect.php';
global $pdo;

$message = '';
$error_message = '';

$RECEPTIONIST_TABLE = 'Receptionists';
$PASSWORD_COLUMN = 'hashed_pass';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_account'])) {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone_number = trim($_POST['phone_number'] ?? '');

    if (empty($full_name) || empty($email) || empty($password) || empty($phone_number)) {
        $error_message = 'Vui lòng nhập đầy đủ Họ tên, Email, SĐT và Mật khẩu.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } else {
        try {
            $sql_check = "SELECT COUNT(*) FROM {$RECEPTIONIST_TABLE} WHERE email = :email";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute(['email' => $email]);
            if ($stmt_check->fetchColumn() > 0) {
                $error_message = 'Email này đã được sử dụng cho một tài khoản Lễ tân khác.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $sql_insert = "
                    INSERT INTO {$RECEPTIONIST_TABLE} (full_name, email, phone_number, {$PASSWORD_COLUMN}, created_at) 
                    VALUES (:full_name, :email, :phone_number, :hashed_pass, NOW())
                ";

                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    'full_name' => $full_name,
                    'email' => $email,
                    'phone_number' => $phone_number,
                    'hashed_pass' => $hashed_password
                ]);

                $message = "Đã tạo tài khoản Lễ tân **{$full_name}** thành công!";
            }

        } catch (PDOException $e) {
            $error_message = 'Lỗi hệ thống database: ' . $e->getMessage();
        }
    }
}
?>