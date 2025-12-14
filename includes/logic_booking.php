<?php
require_once 'config/connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';

$error_message = '';
$success_message = '';

$patient_id = $_SESSION['user_id'] ?? null;
$doctor_id = (int) ($_GET['id'] ?? 0);

if (!$patient_id) {
    header('Location: login.php');
    exit;
}

$available_time_slots = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];

global $pdo;

$stmt = $pdo->prepare("SELECT D.full_name AS doctor_name, D.profile_picture, T.department_name
                         FROM Doctors D JOIN Departments T ON D.department_id = T.department_id
                         WHERE D.doctor_id = :id");
$stmt->execute(['id' => $doctor_id]);
$doctor = $stmt->fetch();
if (!$doctor) {
    header('Location: tim-bac-si.php');
    exit;
}

$stmt = $pdo->prepare("SELECT full_name, phone_number, date_of_birth, gender FROM Patients WHERE patient_id = :id");
$stmt->execute(['id' => $patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

$patient_name = htmlspecialchars($patient['full_name'] ?? '');
$patient_phone = htmlspecialchars($patient['phone_number'] ?? '');
$patient_dob = htmlspecialchars($patient['date_of_birth'] ?? 'Chưa cập nhật');

$gender_display = match ($patient['gender'] ?? '') {
    'Male' => 'Nam',
    'Female' => 'Nữ',
    'Other' => 'Khác',
    default => 'Chưa cập nhật'
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_appointment'])) {

    $appointment_date = $_POST['ngay_kham'] ?? '';
    $appointment_time = $_POST['gio_kham_slot'] ?? '';
    $reason = trim($_POST['ly_do'] ?? '');

    if (!$appointment_date || !$appointment_time || !$reason) {
        $error_message = 'Vui lòng điền đầy đủ thông tin.';
    }

    if (preg_match('/^\d{2}:\d{2}$/', $appointment_time)) {
        $appointment_time .= ':00';
    }

    if (!DateTime::createFromFormat('Y-m-d', $appointment_date)) {
        $error_message = 'Ngày khám không hợp lệ.';
    }

    if (!$error_message) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Appointments
                (patient_id, doctor_id, appointment_date, appointment_time, reason_for_visit, status, created_at)
                VALUES (:p, :d, :ad, :at, :r, 'Pending', NOW())");

            $stmt->execute([
                'p' => $patient_id,
                'd' => $doctor_id,
                'ad' => $appointment_date,
                'at' => $appointment_time,
                'r' => $reason
            ]);

            $new_appointment_id = $pdo->lastInsertId();

            $options = array(
                'cluster' => 'ap1',
                'useTLS' => true
            );

            $pusher = new Pusher\Pusher(
                '18b40fb67053da5ad353',
                'f161fb27583a8016c4dc',
                '2090933',
                $options
            );

            $data['message'] = 'Khách hàng ' . $patient_name . ' vừa đặt lịch lúc ' . substr($appointment_time, 0, 5);
            $data['patient_name'] = $patient_name;
            $data['appointment_id'] = $new_appointment_id;

            $pusher->trigger('phong-kham', 'don-hang-moi', $data);

            $success_message = 'success';

        } catch (PDOException $e) {
            $error_message = $e->getMessage();
        } catch (Exception $e) {
            $error_message = "Lỗi thông báo: " . $e->getMessage();
            if (isset($new_appointment_id))
                $success_message = 'success';
        }
    }
}
?>