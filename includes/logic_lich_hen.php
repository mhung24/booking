<?php
require_once 'config/connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode('lich-hen.php'));
    exit();
}

$patient_id = $_SESSION['user_id'];
$page_title = "Lịch Hẹn Của Tôi";

global $pdo;

$cancel_message = '';

// ===== 1. XỬ LÝ THÔNG BÁO SAU KHI REDIRECT (MỚI THÊM) =====
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'cancel_success') {
        $cancel_message = ['type' => 'success', 'text' => 'Lịch hẹn đã được hủy thành công.'];
    } elseif ($_GET['msg'] == 'cancel_error') {
        $cancel_message = ['type' => 'danger', 'text' => 'Không thể hủy lịch hẹn này (Lỗi hệ thống hoặc trạng thái không hợp lệ).'];
    }
}

// ===== 2. XỬ LÝ HỦY LỊCH (POST) =====
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_appointment'])) {
    $appointment_id_to_cancel = (int) $_POST['appointment_id'];

    try {
        // Cập nhật trạng thái trong Database
        $sql_cancel = "
            UPDATE Appointments 
            SET status = 'Cancelled'
            WHERE appointment_id = :app_id 
              AND patient_id = :patient_id 
              AND status = 'Pending'
        ";
        $stmt_cancel = $pdo->prepare($sql_cancel);
        $stmt_cancel->execute([
            ':app_id' => $appointment_id_to_cancel,
            ':patient_id' => $patient_id
        ]);

        if ($stmt_cancel->rowCount() > 0) {

            // --- GỬI THÔNG BÁO PUSHER ---
            try {
                require __DIR__ . '/../vendor/autoload.php';

                $stmt_name = $pdo->prepare("SELECT full_name FROM Patients WHERE patient_id = ?");
                $stmt_name->execute([$patient_id]);
                $patient_name_pusher = $stmt_name->fetchColumn() ?: "Khách hàng";

                $options = array('cluster' => 'ap1', 'useTLS' => true);

                $pusher = new Pusher\Pusher(
                    '18b40fb67053da5ad353', // Key
                    'f161fb27583a8016c4dc', // Secret
                    '2090933',              // App ID
                    $options
                );

                $data_cancel['message'] = "$patient_name_pusher vừa HỦY lịch hẹn #$appointment_id_to_cancel";
                $data_cancel['type'] = 'cancel';

                $pusher->trigger('phong-kham', 'huy-lich', $data_cancel);
            } catch (Exception $e) {
                // Pusher lỗi thì kệ, vẫn cho hủy thành công
            }
            // -----------------------------

            // [QUAN TRỌNG] Chuyển hướng để làm sạch URL (Post-Redirect-Get)
            header("Location: lich-hen.php?msg=cancel_success");
            exit();

        } else {
            // Hủy thất bại
            header("Location: lich-hen.php?msg=cancel_error");
            exit();
        }
    } catch (PDOException $e) {
        $cancel_message = ['type' => 'danger', 'text' => 'Lỗi hệ thống: ' . $e->getMessage()];
    }
}

// ===== 3. LẤY DANH SÁCH LỊCH HẸN (GET) =====
$sql_appointments = "
    SELECT 
        A.appointment_id, A.appointment_date, A.appointment_time, A.reason_for_visit, A.status,
        D.full_name AS doctor_name, 
        T.department_name
    FROM Appointments A
    JOIN Doctors D ON A.doctor_id = D.doctor_id
    JOIN Departments T ON D.department_id = T.department_id
    WHERE A.patient_id = :patient_id
    ORDER BY A.appointment_date DESC, A.appointment_time DESC
";
$stmt_appointments = $pdo->prepare($sql_appointments);
$stmt_appointments->execute([':patient_id' => $patient_id]);
$appointments = $stmt_appointments->fetchAll();

function get_status_badge(string $status): string
{
    switch ($status) {
        case 'Pending':
            $class = 'bg-warning text-dark';
            $text = 'Chờ xác nhận';
            break;

        case 'Scheduled':
        case 'Confirmed':
            $class = 'bg-success';
            $text = 'Đã xác nhận';
            break;

        case 'Completed':
            $class = 'bg-primary';
            $text = 'Đã hoàn thành';
            break;

        case 'Cancelled':
            $class = 'bg-danger';
            $text = 'Đã hủy';
            break;

        default:
            $class = 'bg-secondary';
            $text = 'Không rõ';
    }
    return "<span class='badge {$class}'>{$text}</span>";
}