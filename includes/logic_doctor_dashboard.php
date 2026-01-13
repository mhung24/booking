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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_exam') {
    $app_id = (int) $_POST['appointment_id'];
    $diagnosis = $_POST['diagnosis'] ?? '';

    $med_ids = $_POST['med_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $dosages = $_POST['dosage'] ?? [];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE Appointments SET status = 'Completed', diagnosis = :diag WHERE appointment_id = :id");
        $stmt->execute(['diag' => $diagnosis, 'id' => $app_id]);

        if (!empty($med_ids)) {
            $sql_pres = "INSERT INTO Prescription_Details (appointment_id, medicine_id, quantity, dosage) VALUES (:aid, :mid, :qty, :dose)";
            $stmt_pres = $pdo->prepare($sql_pres);

            for ($i = 0; $i < count($med_ids); $i++) {
                if (!empty($med_ids[$i])) {
                    $stmt_pres->execute([
                        'aid' => $app_id,
                        'mid' => $med_ids[$i],
                        'qty' => $quantities[$i],
                        'dose' => $dosages[$i]
                    ]);
                }
            }
        }

        $pdo->commit();
        header("Location: doctor_dashboard.php?msg=completed");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Lỗi lưu đơn thuốc: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'transfer') {
    $app_id = (int) $_POST['appointment_id'];
    $target_doctor_id = (int) $_POST['target_doctor_id'];
    $stmt = $pdo->prepare("UPDATE Appointments SET doctor_id = :did, status = 'Waiting', queued_at = NOW() WHERE appointment_id = :aid");
    $stmt->execute(['did' => $target_doctor_id, 'aid' => $app_id]);
    header("Location: doctor_dashboard.php?msg=transfer_success");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_schedule') {
    $work_date = $_POST['work_date'] ?? '';
    $slots = $_POST['slots'] ?? [];

    if (!empty($work_date) && !empty($slots)) {
        try {
            foreach ($slots as $slot_name) {
                $sql_check = "SELECT COUNT(*) FROM doctor_schedules WHERE doctor_id = :d_id AND work_date = :w_date AND slot_name = :s_name";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->execute([
                    ':d_id' => $current_doctor_id,
                    ':w_date' => $work_date,
                    ':s_name' => $slot_name
                ]);

                if ($stmt_check->fetchColumn() == 0) {
                    $sql_insert = "INSERT INTO doctor_schedules (doctor_id, work_date, slot_name) VALUES (:d_id, :w_date, :s_name)";
                    $stmt_insert = $pdo->prepare($sql_insert);
                    $stmt_insert->execute([
                        ':d_id' => $current_doctor_id,
                        ':w_date' => $work_date,
                        ':s_name' => $slot_name
                    ]);
                }
            }
            header("Location: doctor_dashboard.php?msg=schedule_success");
            exit;
        } catch (Exception $e) {
            die("Lỗi lưu lịch làm việc: " . $e->getMessage());
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'call' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE Appointments SET status = 'Examining' WHERE appointment_id = :id");
    $stmt->execute(['id' => (int) $_GET['id']]);
    header("Location: doctor_dashboard.php");
    exit;
}

try {
    $sql_examining = "
        SELECT A.*, P.full_name, P.phone_number, P.bhyt_code, P.address, P.date_of_birth, P.gender, S.service_name 
        FROM Appointments A
        JOIN Patients P ON A.patient_id = P.patient_id
        LEFT JOIN Services S ON A.service_id = S.service_id
        WHERE A.status = 'Examining' AND A.doctor_id = :did
    ";
    $stmt = $pdo->prepare($sql_examining);
    $stmt->execute(['did' => $current_doctor_id]);
    $examining_patient = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql_waiting = "
        SELECT A.*, P.full_name, P.phone_number, S.service_name 
        FROM Appointments A
        JOIN Patients P ON A.patient_id = P.patient_id
        LEFT JOIN Services S ON A.service_id = S.service_id
        WHERE A.status = 'Waiting' AND A.doctor_id = :did
        ORDER BY A.queued_at ASC
    ";
    $stmt = $pdo->prepare($sql_waiting);
    $stmt->execute(['did' => $current_doctor_id]);
    $waiting_patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $medicines = $pdo->query("SELECT * FROM Medicines ORDER BY medicine_name ASC")->fetchAll(PDO::FETCH_ASSOC);

    $stmt_docs = $pdo->prepare("SELECT D.doctor_id, D.full_name, T.department_name FROM Doctors D JOIN Departments T ON D.department_id = T.department_id WHERE D.doctor_id != :did");
    $stmt_docs->execute(['did' => $current_doctor_id]);
    $other_doctors = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Lỗi kết nối: " . $e->getMessage());
}

if (isset($_GET['action']) && $_GET['action'] === 'get_history' && isset($_GET['patient_id'])) {
    $pid = (int) $_GET['patient_id'];

    $sql = "
        SELECT A.*, D.full_name as doctor_name, S.service_name
        FROM Appointments A
        JOIN Doctors D ON A.doctor_id = D.doctor_id
        LEFT JOIN Services S ON A.service_id = S.service_id
        WHERE A.patient_id = :pid 
        AND A.status = 'Completed'
        ORDER BY A.appointment_date DESC, A.appointment_time DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['pid' => $pid]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($history)) {
        echo '<div class="text-center text-muted py-5"><i class="fas fa-file-medical-alt fa-3x mb-3 opacity-50"></i><br>Bệnh nhân chưa có lịch sử khám bệnh.</div>';
    } else {
        echo '<div class="timeline">';
        foreach ($history as $h) {
            $stmt_med = $pdo->prepare("
                SELECT pd.*, m.medicine_name, m.unit 
                FROM Prescription_Details pd
                JOIN Medicines m ON pd.medicine_id = m.medicine_id
                WHERE pd.appointment_id = :aid
            ");
            $stmt_med->execute(['aid' => $h['appointment_id']]);
            $meds = $stmt_med->fetchAll(PDO::FETCH_ASSOC);

            echo '
            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-primary mb-0">' . date('d/m/Y', strtotime($h['appointment_date'])) . ' <small class="text-muted fw-normal">(' . date('H:i', strtotime($h['appointment_time'])) . ')</small></h6>
                        <span class="badge bg-light text-dark border">BS. ' . $h['doctor_name'] . '</span>
                    </div>
                    
                    <div class="mb-2">
                        <strong class="text-dark"><i class="fas fa-stethoscope me-1"></i> Chẩn đoán:</strong>
                        <div class="bg-light p-2 rounded mt-1 fst-italic text-dark">
                            ' . nl2br(htmlspecialchars($h['diagnosis'])) . '
                        </div>
                    </div>';

            if (!empty($meds)) {
                echo '<div class="mt-2">
                        <strong class="text-success"><i class="fas fa-pills me-1"></i> Đơn thuốc:</strong>
                        <ul class="list-group list-group-flush mt-1 small">';
                foreach ($meds as $m) {
                    echo '<li class="list-group-item px-0 py-1 bg-transparent border-0">
                            - <strong>' . $m['medicine_name'] . '</strong> 
                            (SL: ' . $m['quantity'] . ' ' . $m['unit'] . ') 
                            <span class="text-muted">- ' . $m['dosage'] . '</span>
                          </li>';
                }
                echo '</ul></div>';
            }

            echo '</div></div>';
        }
        echo '</div>';
    }
    exit;
}
?>