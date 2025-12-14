<?php
require_once 'config/connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode('thong-tin-ca-nhan.php'));
    exit();
}

$patient_id = $_SESSION['user_id'];
$page_title = "Quản Lý Hồ Sơ Cá Nhân";

global $pdo;

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {

    $email = trim($_POST['email']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        $error_message = "Địa chỉ email không hợp lệ.";
    } else {
        try {
            $sql_update = "
                UPDATE Patients 
                SET email = :email, 
                    date_of_birth = :dob, 
                    gender = :gender, 
                    address = :address
                WHERE patient_id = :patient_id
            ";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                ':email' => empty($email) ? NULL : $email,
                ':dob' => empty($date_of_birth) ? NULL : $date_of_birth,
                ':gender' => empty($gender) ? NULL : $gender,
                ':address' => $address,
                ':patient_id' => $patient_id
            ]);

            $success_message = "Thông tin hồ sơ đã được cập nhật thành công.";

        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error_message = "Email này đã được sử dụng bởi tài khoản khác. Vui lòng chọn email khác.";
            } else {
                $error_message = "Lỗi hệ thống khi cập nhật: " . $e->getMessage();
            }
        }
    }
}

$sql_patient = "
    SELECT full_name, phone_number, email, date_of_birth, gender, address
    FROM Patients
    WHERE patient_id = :patient_id
";
$stmt_patient = $pdo->prepare($sql_patient);
$stmt_patient->execute([':patient_id' => $patient_id]);
$patient = $stmt_patient->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header("Location: logout.php");
    exit();
}

$current_dob = $patient['date_of_birth'];
$current_gender = $patient['gender'];
$current_email = htmlspecialchars($patient['email'] ?? '');
$current_address = htmlspecialchars($patient['address'] ?? '');
?>