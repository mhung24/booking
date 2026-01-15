<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/connect.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Receptionist') {
    header("Location: login.php");
    exit;
}
$receptionist_name = $_SESSION['receptionist_name'] ?? 'Lễ tân';

global $pdo;
$message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_patient') {
        try {
            $stmt_check = $pdo->prepare("SELECT patient_id FROM Patients WHERE phone_number = :phone");
            $stmt_check->execute(['phone' => $_POST['phone_number']]);
            if ($stmt_check->rowCount() > 0) {
                $error_message = "Số điện thoại này đã được đăng ký cho bệnh nhân khác.";
            } else {
                $default_pass = password_hash('123456', PASSWORD_DEFAULT);
                $sql = "INSERT INTO Patients (full_name, phone_number, gender, date_of_birth, bhyt_code, address, password) 
                        VALUES (:name, :phone, :gender, :dob, :bhyt, :addr, :pass)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'name' => $_POST['full_name'],
                    'phone' => $_POST['phone_number'],
                    'gender' => $_POST['gender'],
                    'dob' => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
                    'bhyt' => $_POST['bhyt_code'],
                    'addr' => $_POST['address'],
                    'pass' => $default_pass
                ]);
                $message = "Thêm hồ sơ bệnh nhân thành công!";
            }
        } catch (Exception $e) {
            $error_message = "Lỗi: " . $e->getMessage();
        }
    }

    if ($action === 'edit_patient') {
        try {
            $sql = "UPDATE Patients SET full_name = :name, phone_number = :phone, gender = :gender, 
                    date_of_birth = :dob, bhyt_code = :bhyt, address = :addr 
                    WHERE patient_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'name' => $_POST['full_name'],
                'phone' => $_POST['phone_number'],
                'gender' => $_POST['gender'],
                'dob' => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
                'bhyt' => $_POST['bhyt_code'],
                'addr' => $_POST['address'],
                'id' => $_POST['patient_id']
            ]);
            $message = "Cập nhật hồ sơ thành công!";
        } catch (Exception $e) {
            $error_message = "Lỗi: " . $e->getMessage();
        }
    }

    if ($action === 'receive_patient') {
        try {
            $pid = $_POST['patient_id'];
            $did = $_POST['doctor_id'] ?? null;

            if (!$did) {
                $error_message = "Lỗi: Chưa chọn bác sĩ tiếp nhận.";
            } else {
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Appointments WHERE patient_id = ? AND appointment_date = CURDATE() AND status IN ('Waiting', 'Examining')");
                $stmt_check->execute([$pid]);

                if ($stmt_check->fetchColumn() > 0) {
                    $error_message = "Bệnh nhân này đã có tên trong danh sách chờ hoặc đang khám hôm nay.";
                } else {
                    $sql = "INSERT INTO Appointments (patient_id, doctor_id, appointment_date, appointment_time, status, created_at, queued_at) 
                            VALUES (:pid, :did, CURDATE(), CURTIME(), 'Waiting', NOW(), NOW())";
                    $pdo->prepare($sql)->execute(['pid' => $pid, 'did' => $did]);
                    $message = "Tiếp nhận thành công! Bệnh nhân đã vào hàng đợi.";
                }
            }
        } catch (Exception $e) {
            $error_message = "Lỗi: " . $e->getMessage();
        }
    }

    if ($action === 'rebook_reexam') {
        try {
            $pid = $_POST['patient_id'];
            $did = $_POST['doctor_id'];
            $adate = $_POST['appointment_date'];
            $atime = $_POST['appointment_time'];

            // 1. Chặn đặt trùng giờ cho cùng 1 bác sĩ
            $stmt_dup = $pdo->prepare("SELECT COUNT(*) FROM Appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'Cancelled'");
            $stmt_dup->execute([$did, $adate, $atime]);

            if ($stmt_dup->fetchColumn() > 0) {
                $error_message = "Bác sĩ đã có lịch hẹn vào khung giờ này. Vui lòng chọn giờ khác.";
            } else {
                $pdo->beginTransaction();
                // 2. Chèn lịch mới
                $sql = "INSERT INTO Appointments (patient_id, doctor_id, appointment_date, appointment_time, status, created_at) 
                        VALUES (:pid, :did, :adate, :atime, 'Scheduled', NOW())";
                $pdo->prepare($sql)->execute(['pid' => $pid, 'did' => $did, 'adate' => $adate, 'atime' => $atime]);

                // 3. Đánh dấu ca cũ đã được hẹn lại (Tránh hiện trong list cần gọi)
                $stmt_up_old = $pdo->prepare("UPDATE Appointments SET re_exam_date = NULL WHERE patient_id = ? AND status = 'Completed' AND re_exam_date IS NOT NULL");
                $stmt_up_old->execute([$pid]);

                $pdo->commit();
                $message = "Đã lên lịch tái khám thành công!";
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction())
                $pdo->rollBack();
            $error_message = "Lỗi đặt lịch: " . $e->getMessage();
        }
    }
}

// DOANH THU CHI TIẾT
$today_revenue = $pdo->query("SELECT SUM(paid_amount) FROM Appointments WHERE appointment_date = CURDATE() AND status = 'Completed'")->fetchColumn() ?: 0;
$month_revenue = $pdo->query("SELECT SUM(paid_amount) FROM Appointments WHERE MONTH(appointment_date) = MONTH(CURDATE()) AND YEAR(appointment_date) = YEAR(CURDATE()) AND status = 'Completed'")->fetchColumn() ?: 0;

$stmt_rev_detail = $pdo->query("
    SELECT P.full_name, A.paid_amount, A.appointment_id, S.service_name as main_service,
    (SELECT GROUP_CONCAT(sv.service_name SEPARATOR ', ') 
     FROM Service_Requests sr 
     JOIN Services sv ON sr.service_id = sv.service_id 
     WHERE sr.appointment_id = A.appointment_id) as extra_services
    FROM Appointments A 
    JOIN Patients P ON A.patient_id = P.patient_id 
    LEFT JOIN Services S ON A.service_id = S.service_id
    WHERE A.appointment_date = CURDATE() AND A.status = 'Completed'
    ORDER BY A.appointment_id DESC
");
$revenue_details = $stmt_rev_detail->fetchAll(PDO::FETCH_ASSOC);
$total_completed_today = count($revenue_details);

// DANH SÁCH TÁI KHÁM (Chỉ hiện khi có chỉ định và chưa được đặt lịch mới)
$stmt_reexam = $pdo->prepare("
    SELECT A.re_exam_date, A.patient_id, A.doctor_id, A.diagnosis, P.full_name, P.phone_number, D.full_name as doctor_name 
    FROM Appointments A 
    JOIN Patients P ON A.patient_id = P.patient_id 
    JOIN Doctors D ON A.doctor_id = D.doctor_id 
    WHERE A.re_exam_date = CURDATE() 
    AND A.status = 'Completed'
    AND NOT EXISTS (
        SELECT 1 FROM Appointments A2 
        WHERE A2.patient_id = A.patient_id 
        AND A2.appointment_date >= CURDATE() 
        AND A2.status IN ('Pending', 'Scheduled', 'Waiting')
    )
");
$stmt_reexam->execute();
$reexam_list = $stmt_reexam->fetchAll(PDO::FETCH_ASSOC);

// TÌM KIẾM HỒ SƠ
$keyword = $_GET['search'] ?? '';
$sql = "SELECT * FROM Patients WHERE 1=1";
$params = [];
if (!empty($keyword)) {
    $sql .= " AND (full_name LIKE :kw OR phone_number LIKE :kw OR bhyt_code LIKE :kw)";
    $params['kw'] = "%$keyword%";
}
$sql .= " ORDER BY patient_id DESC LIMIT 50";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);