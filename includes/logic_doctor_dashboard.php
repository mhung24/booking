<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/connect.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

$current_doctor_id = $_SESSION['doctor_id'] ?? 1;
global $pdo;

$stmt_name = $pdo->prepare("SELECT full_name FROM Doctors WHERE doctor_id = :did");
$stmt_name->execute(['did' => $current_doctor_id]);
$doctor_name_display = $stmt_name->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'complete_exam') {
        $app_id = (int) $_POST['appointment_id'];
        $diagnosis = $_POST['diagnosis'] ?? '';
        $re_exam_date = !empty($_POST['re_exam_date']) ? $_POST['re_exam_date'] : null;
        $med_ids = $_POST['med_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $dosages = $_POST['dosage'] ?? [];

        try {
            $pdo->beginTransaction();
            $total_payment = 300000;

            $stmt_main_price = $pdo->prepare("SELECT price FROM Services S JOIN Appointments A ON S.service_id = A.service_id WHERE A.appointment_id = :aid");
            $stmt_main_price->execute(['aid' => $app_id]);
            $total_payment += (float) $stmt_main_price->fetchColumn();

            $stmt_cls_price = $pdo->prepare("SELECT SUM(s.price) FROM Service_Requests sr JOIN Services s ON sr.service_id = s.service_id WHERE sr.appointment_id = :aid AND sr.status != 'Cancelled'");
            $stmt_cls_price->execute(['aid' => $app_id]);
            $total_payment += (float) $stmt_cls_price->fetchColumn();

            // CHUẨN HÓA THAM SỐ: Đảm bảo bảng Appointments CÓ cột re_exam_date
            $stmt = $pdo->prepare("UPDATE Appointments SET status = 'Completed', diagnosis = :diag, paid_amount = :total, re_exam_date = :re_date WHERE appointment_id = :id");
            $stmt->execute([
                'diag' => $diagnosis,
                'total' => $total_payment,
                're_date' => $re_exam_date,
                'id' => $app_id
            ]);

            $pdo->prepare("UPDATE Service_Requests SET status = 'Completed' WHERE appointment_id = :aid AND status = 'Pending'")->execute(['aid' => $app_id]);

            if (!empty($med_ids)) {
                $stmt_pres = $pdo->prepare("INSERT INTO Prescription_Details (appointment_id, medicine_id, quantity, dosage) VALUES (:aid, :mid, :qty, :dose)");
                for ($i = 0; $i < count($med_ids); $i++) {
                    if (!empty($med_ids[$i])) {
                        $stmt_pres->execute(['aid' => $app_id, 'mid' => $med_ids[$i], 'qty' => $quantities[$i], 'dose' => $dosages[$i]]);
                    }
                }
            }
            $pdo->commit();
            header("Location: doctor_dashboard.php?msg=completed");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Lỗi hoàn thành khám: " . $e->getMessage());
        }
    }

    if ($action === 'request_services') {
        $app_id = (int) $_POST['appointment_id'];
        $service_ids = $_POST['service_ids'] ?? [];
        try {
            $pdo->beginTransaction();
            $stmt_app_data = $pdo->prepare("SELECT patient_id FROM Appointments WHERE appointment_id = :aid");
            $stmt_app_data->execute(['aid' => $app_id]);
            $patient_id = $stmt_app_data->fetchColumn();

            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Service_Requests WHERE appointment_id = :aid AND service_id = :sid AND status = 'Pending'");
            $stmt_req = $pdo->prepare("INSERT INTO Service_Requests (appointment_id, patient_id, doctor_id, service_id, status) VALUES (:aid, :pid, :did, :sid, 'Pending')");

            foreach ($service_ids as $sid) {
                $stmt_check->execute(['aid' => $app_id, 'sid' => $sid]);
                if ($stmt_check->fetchColumn() == 0) {
                    $stmt_req->execute(['aid' => $app_id, 'pid' => $patient_id, 'did' => $current_doctor_id, 'sid' => $sid]);
                }
            }

            $pdo->prepare("UPDATE Appointments SET status = 'WaitingResults' WHERE appointment_id = :aid")->execute(['aid' => $app_id]);
            $pdo->commit();
            header("Location: doctor_dashboard.php?msg=requested");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Lỗi yêu cầu CLS: " . $e->getMessage());
        }
    }

    if ($action === 'report_off') {
        $off_date = $_POST['off_date'] ?? '';
        $slot = $_POST['slot_name'] ?? '';
        try {
            $pdo->beginTransaction();
            // BS báo nghỉ: Chuyển trạng thái sang Inactive
            $stmt_off = $pdo->prepare("UPDATE doctor_schedules SET status = 'Inactive' WHERE doctor_id = ? AND work_date = ? AND slot_name = ?");
            $stmt_off->execute([$current_doctor_id, $off_date, $slot]);

            // Tìm BS thay thế cùng khoa, ngẫu nhiên, chưa có lịch trực hôm đó
            $stmt_replace = $pdo->prepare("
                SELECT D.doctor_id 
                FROM Doctors D
                WHERE D.department_id = (SELECT department_id FROM Doctors WHERE doctor_id = ?)
                AND D.doctor_id != ?
                AND D.doctor_id NOT IN (
                    SELECT doctor_id FROM doctor_schedules 
                    WHERE work_date = ? AND slot_name = ? AND status = 'Active'
                )
                ORDER BY RAND() LIMIT 1
            ");
            $stmt_replace->execute([$current_doctor_id, $current_doctor_id, $off_date, $slot]);
            $replacement_id = $stmt_replace->fetchColumn();

            if ($replacement_id) {
                $stmt_insert = $pdo->prepare("INSERT INTO doctor_schedules (doctor_id, work_date, slot_name, status) VALUES (?, ?, ?, 'Active')");
                $stmt_insert->execute([$replacement_id, $off_date, $slot]);
            }
            $pdo->commit();
            header("Location: doctor_dashboard.php?msg=off_success");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Lỗi báo nghỉ: " . $e->getMessage());
        }
    }

    if ($action === 'transfer') {
        $app_id = (int) $_POST['appointment_id'];
        $target_doctor_id = (int) $_POST['target_doctor_id'];
        $pdo->prepare("UPDATE Appointments SET doctor_id = :did, status = 'Waiting', queued_at = NOW() WHERE appointment_id = :aid")->execute(['did' => $target_doctor_id, 'aid' => $app_id]);
        header("Location: doctor_dashboard.php?msg=transfer_success");
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'call' && isset($_GET['id'])) {
    $pdo->prepare("UPDATE Appointments SET status = 'Examining' WHERE appointment_id = :id")->execute(['id' => (int) $_GET['id']]);
    header("Location: doctor_dashboard.php?id=" . (int) $_GET['id']);
    exit;
}

try {
    $target_id = (int) ($_GET['id'] ?? 0);
    if ($target_id > 0) {
        $sql_examining = "SELECT A.*, P.full_name, P.phone_number, P.bhyt_code, P.address, P.date_of_birth, P.gender, S.service_name, S.price as service_price 
                          FROM Appointments A JOIN Patients P ON A.patient_id = P.patient_id LEFT JOIN Services S ON A.service_id = S.service_id 
                          WHERE A.appointment_id = :tid AND A.doctor_id = :did AND A.status != 'Completed'";
        $stmt = $pdo->prepare($sql_examining);
        $stmt->execute(['tid' => $target_id, 'did' => $current_doctor_id]);
    } else {
        $sql_examining = "SELECT A.*, P.full_name, P.phone_number, P.bhyt_code, P.address, P.date_of_birth, P.gender, S.service_name, S.price as service_price 
                          FROM Appointments A JOIN Patients P ON A.patient_id = P.patient_id LEFT JOIN Services S ON A.service_id = S.service_id 
                          WHERE A.doctor_id = :did AND A.status = 'Examining' LIMIT 1";
        $stmt = $pdo->prepare($sql_examining);
        $stmt->execute(['did' => $current_doctor_id]);
    }
    $examining_patient = $stmt->fetch(PDO::FETCH_ASSOC);

    $waiting_patients = $pdo->prepare("SELECT A.*, P.full_name, S.service_name FROM Appointments A JOIN Patients P ON A.patient_id = P.patient_id LEFT JOIN Services S ON A.service_id = S.service_id WHERE A.status = 'Waiting' AND A.doctor_id = ? ORDER BY A.queued_at ASC");
    $waiting_patients->execute([$current_doctor_id]);
    $waiting_patients = $waiting_patients->fetchAll(PDO::FETCH_ASSOC);

    $stmt_waiting_cls = $pdo->prepare("SELECT DISTINCT A.appointment_id, P.full_name FROM Service_Requests SR JOIN Appointments A ON SR.appointment_id = A.appointment_id JOIN Patients P ON A.patient_id = P.patient_id WHERE SR.doctor_id = :did AND SR.status = 'Pending'");
    $stmt_waiting_cls->execute(['did' => $current_doctor_id]);
    $waiting_results_list = $stmt_waiting_cls->fetchAll(PDO::FETCH_ASSOC);

    $services_list = $pdo->query("SELECT * FROM Services ORDER BY service_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $medicines = $pdo->query("SELECT * FROM Medicines ORDER BY medicine_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $other_doctors = $pdo->prepare("SELECT D.doctor_id, D.full_name, T.department_name as dept FROM Doctors D JOIN Departments T ON D.department_id = T.department_id WHERE D.doctor_id != ?");
    $other_doctors->execute([$current_doctor_id]);
    $other_doctors = $other_doctors->fetchAll(PDO::FETCH_ASSOC);

    // Lấy toàn bộ lịch trực từ ngày hiện tại trở đi
    $stmt_sch = $pdo->prepare("SELECT * FROM doctor_schedules WHERE doctor_id = :did AND work_date >= CURDATE() ORDER BY work_date ASC, slot_name ASC");
    $stmt_sch->execute(['did' => $current_doctor_id]);
    $my_schedules = $stmt_sch->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Lỗi kết nối hoặc truy vấn.");
}

if (isset($_GET['action']) && $_GET['action'] === 'get_history' && isset($_GET['patient_id'])) {
    $pid = (int) $_GET['patient_id'];
    $stmt = $pdo->prepare("SELECT A.*, D.full_name as doctor_name FROM Appointments A JOIN Doctors D ON A.doctor_id = D.doctor_id WHERE A.patient_id = :pid AND A.status = 'Completed' ORDER BY A.appointment_date DESC LIMIT 10");
    $stmt->execute(['pid' => $pid]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($history))
        echo "Bệnh nhân chưa có lịch sử.";
    else
        foreach ($history as $h)
            echo "<div class='border-bottom mb-2 pb-2 small'><strong>" . date('d/m/Y', strtotime($h['appointment_date'])) . "</strong>: " . htmlspecialchars($h['diagnosis']) . "</div>";
    exit;
}