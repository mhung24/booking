<?php
require_once 'config/connect.php';
require __DIR__ . '/../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$patient_id = $_SESSION['user_id'];
$doctor_id = (int) ($_POST['current_doctor_id'] ?? ($_GET['id'] ?? 0));

$error_message = '';
$success_message = '';
global $pdo;

$stmt_p = $pdo->prepare("SELECT full_name, phone_number, date_of_birth, gender FROM Patients WHERE patient_id = ?");
$stmt_p->execute([$patient_id]);
$patient = $stmt_p->fetch(PDO::FETCH_ASSOC);

$patient_name = $patient['full_name'] ?? '';
$patient_phone = $patient['phone_number'] ?? '';
$patient_dob = $patient['date_of_birth'] ? date('d/m/Y', strtotime($patient['date_of_birth'])) : '';
$gender_display = match ($patient['gender'] ?? '') {
    'Male' => 'Nam',
    'Female' => 'Nữ',
    'Other' => 'Khác',
    default => 'Chưa cập nhật'
};

if ($doctor_id > 0) {
    $stmt_d = $pdo->prepare("
        SELECT d.doctor_id, d.full_name as doctor_name, d.profile_picture, dp.department_name
        FROM Doctors d
        JOIN Departments dp ON d.department_id = dp.department_id
        WHERE d.doctor_id = ?
    ");
    $stmt_d->execute([$doctor_id]);
    $doctor = $stmt_d->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        header("Location: tim-bac-si.php");
        exit;
    }
} else {
    header("Location: tim-bac-si.php");
    exit;
}

$available_time_slots = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_appointment'])) {
    $appointment_date = $_POST['ngay_kham'] ?? '';
    $appointment_time = $_POST['gio_kham_slot'] ?? '';
    $reason = trim($_POST['ly_do'] ?? '');

    if (empty($appointment_date) || empty($appointment_time) || empty($reason)) {
        $error_message = 'Vui lòng điền đầy đủ thông tin ngày khám, giờ khám và lý do.';
    }

    if (!$error_message) {
        $stmt_exists = $pdo->prepare("SELECT COUNT(*) FROM Appointments 
                                     WHERE patient_id = :p 
                                     AND doctor_id = :d 
                                     AND status IN ('Pending', 'Confirmed')
                                     AND appointment_date >= CURDATE()");
        $stmt_exists->execute([
            'p' => $patient_id,
            'd' => $doctor_id
        ]);

        if ($stmt_exists->fetchColumn() > 0) {
            $error_message = 'Bạn đã có một lịch hẹn đang chờ xử lý với bác sĩ này.';
        }
    }

    if (!$error_message) {
        $hour = (int) substr($appointment_time, 0, 2);
        $slot_needed = ($hour < 12) ? 'Sáng' : 'Chiều';

        $stmt_work = $pdo->prepare("SELECT COUNT(*) FROM doctor_schedules 
                                    WHERE doctor_id = :d 
                                    AND work_date = :ad 
                                    AND slot_name = :s 
                                    AND status = 'Active'");
        $stmt_work->execute([
            'd' => $doctor_id,
            'ad' => $appointment_date,
            's' => $slot_needed
        ]);

        if ($stmt_work->fetchColumn() == 0) {
            $error_message = 'Bác sĩ không có lịch làm việc vào ca ' . $slot_needed . ' ngày này.';
        }
    }

    if (!$error_message) {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Appointments 
                                     WHERE doctor_id = :d 
                                     AND appointment_date = :ad 
                                     AND appointment_time = :at 
                                     AND status != 'Cancelled'");
        $stmt_check->execute([
            'd' => $doctor_id,
            'ad' => $appointment_date,
            'at' => $appointment_time
        ]);

        if ($stmt_check->fetchColumn() > 0) {
            $error_message = 'Khung giờ này đã có người đặt.';
        }
    }

    if (!$error_message) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Appointments
                (patient_id, doctor_id, appointment_date, appointment_time, reason_for_visit, status, is_walkin, created_at)
                VALUES (:p, :d, :ad, :at, :r, 'Pending', 0, NOW())");

            $result = $stmt->execute([
                'p' => $patient_id,
                'd' => $doctor_id,
                'ad' => $appointment_date,
                'at' => $appointment_time,
                'r' => $reason
            ]);

            if ($result) {
                $new_appointment_id = $pdo->lastInsertId();
                try {
                    $options = ['cluster' => 'ap1', 'useTLS' => true];
                    $pusher = new Pusher\Pusher(
                        '18b40fb67053da5ad353',
                        'f161fb27583a8016c4dc',
                        '2090933',
                        $options
                    );

                    $data_pusher = [
                        'message' => 'Khách hàng ' . $patient_name . ' vừa đặt lịch lúc ' . substr($appointment_time, 0, 5),
                        'patient_name' => $patient_name,
                        'appointment_id' => $new_appointment_id
                    ];

                    $pusher->trigger('phong-kham', 'don-hang-moi', $data_pusher);
                } catch (Exception $e_pusher) {
                }

                $success_message = 'success';
            } else {
                $error_message = "Không thể lưu lịch hẹn. Vui lòng thử lại.";
            }

        } catch (Exception $e) {
            $error_message = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}