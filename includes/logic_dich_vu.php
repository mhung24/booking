<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config/connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=" . $redirect_url);
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tim-bac-si.php");
    exit();
}

$doctor_id = (int) $_GET['id'];
$patient_id = $_SESSION['user_id'];

global $pdo;

$sql_doctor = "
    SELECT D.full_name AS doctor_name, D.profile_picture, T.department_name
    FROM Doctors D
    JOIN Departments T ON D.department_id = T.department_id
    WHERE D.doctor_id = :id
";
$stmt_doctor = $pdo->prepare($sql_doctor);
$stmt_doctor->execute([':id' => $doctor_id]);
$doctor = $stmt_doctor->fetch();

if (!$doctor) {
    header("Location: tim-bac-si.php");
    exit();
}

$sql_patient = "
    SELECT full_name, phone_number, date_of_birth, gender
    FROM Patients
    WHERE patient_id = :id
";
$stmt_patient = $pdo->prepare($sql_patient);
$stmt_patient->execute([':id' => $patient_id]);
$patient = $stmt_patient->fetch(PDO::FETCH_ASSOC);

$patient_name = htmlspecialchars($patient['full_name'] ?? '');
$patient_phone = htmlspecialchars($patient['phone_number'] ?? '');
$patient_dob = htmlspecialchars($patient['date_of_birth'] ?? '');
$patient_gender = htmlspecialchars($patient['gender'] ?? '');

$gender_display = '';
if ($patient_gender === 'Male') {
    $gender_display = 'Nam';
} elseif ($patient_gender === 'Female') {
    $gender_display = 'Nữ';
} elseif ($patient_gender === 'Other') {
    $gender_display = 'Khác';
}

$available_time_slots = [
    '08:00',
    '09:00',
    '10:00',
    '11:00',
    '14:00',
    '15:00',
    '16:00',
    '17:00'
];

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_appointment'])) {

    $appointment_date = trim($_POST['ngay_kham'] ?? '');
    $appointment_time = trim($_POST['gio_kham_slot'] ?? '');
    $reason = trim($_POST['ly_do'] ?? '');

    if (empty($appointment_date) || empty($appointment_time) || empty($reason)) {
        $error_message = "Vui lòng điền đầy đủ Ngày Khám, Giờ Khám và Lý do Khám.";
    } else {
        try {
            $sql_insert = "
                INSERT INTO Appointments 
                (patient_id, doctor_id, appointment_date, appointment_time, reason_for_visit, status, created_at)
                VALUES (:patient_id, :doctor_id, :app_date, :app_time, :reason, 'Pending', NOW())
            ";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                ':patient_id' => $patient_id,
                ':doctor_id' => $doctor_id,
                ':app_date' => $appointment_date,
                ':app_time' => $appointment_time,
                ':reason' => $reason
            ]);

            $success_message = "Đặt lịch thành công!";

        } catch (PDOException $e) {
            $error_message = "Lỗi SQL: " . $e->getMessage() . " | SQLSTATE: " . $e->getCode() . " | Vui lòng kiểm tra các cột trong bảng Appointments.";
        }
    }
}
?>