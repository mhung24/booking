<?php
require_once 'config/connect.php';
session_start();

$error_message = '';
$success_message = '';

if (isset($_SESSION['doctor_id']) && !empty($_SESSION['doctor_id'])) {
    header("Location: doctor-dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $license_number = trim($_POST['license_number']);
    $password = $_POST['password'];

    if (empty($license_number) || empty($password)) {
        $error_message = "Vui lòng nhập Mã số hành nghề và Mật khẩu.";
    } else {
        global $pdo;

        $sql = "SELECT doctor_id, full_name, password_hash FROM Doctors WHERE license_number = :license_number";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':license_number' => $license_number]);
        $doctor = $stmt->fetch();

        if ($doctor) {
            if (password_verify($password, $doctor['password_hash'])) {

                $_SESSION['doctor_id'] = $doctor['doctor_id'];
                $_SESSION['doctor_name'] = $doctor['full_name'];

                header("Location: doctor_dashboard.php");
                exit();

            } else {
                $error_message = "Mật khẩu không chính xác.";
            }
        } else {
            $error_message = "Mã số hành nghề không đúng hoặc chưa được đăng ký.";
        }
    }
}
?>