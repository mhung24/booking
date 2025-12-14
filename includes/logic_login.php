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
    $phone_number = trim($_POST['phone_number']);
    $password = $_POST['password'];

    if (empty($phone_number) || empty($password)) {
        $error_message = "Vui lòng nhập Số điện thoại và Mật khẩu.";
    } else {
        global $pdo;

        $sql = "SELECT patient_id, full_name, password_hash FROM Patients WHERE phone_number = :phone_number";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':phone_number' => $phone_number]);
        $patient = $stmt->fetch();

        if ($patient) {
            if (password_verify($password, $patient['password_hash'])) {

                $_SESSION['user_id'] = $patient['patient_id'];
                $_SESSION['user_name'] = $patient['full_name'];

                if (isset($_SESSION['redirect_after_login']) && !empty($_SESSION['redirect_after_login'])) {
                    $redirect_url = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header("Location: " . $redirect_url);
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }

            } else {
                $error_message = "Mật khẩu không chính xác.";
            }
        } else {
            $error_message = "Số điện thoại này chưa được đăng ký.";
        }
    }
}
?>