<?php
require_once 'config/connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Receptionist') {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';
$appointment_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

global $pdo;

try {
    $sql = "
        SELECT 
            A.appointment_id, A.appointment_date, A.appointment_time, A.reason_for_visit, A.status, A.paid_amount,
            P.full_name AS patient_name, P.phone_number, P.bhyt_code, P.address,
            D.full_name AS doctor_name, 
            T.department_name,
            S.service_name, S.price AS service_price
        FROM Appointments A
        JOIN Patients P ON A.patient_id = P.patient_id
        JOIN Doctors D ON A.doctor_id = D.doctor_id
        JOIN Departments T ON D.department_id = T.department_id
        LEFT JOIN Services S ON A.service_id = S.service_id
        WHERE A.appointment_id = :id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $appointment_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        die("Không tìm thấy lịch hẹn.");
    }

    if ($data['status'] === 'Confirmed') {
        header("Location: receptionist_dashboard.php");
        exit;
    }

} catch (PDOException $e) {
    die("Lỗi kết nối: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_action'])) {
    try {
        // Sửa 'Confirmed' thành 'Scheduled'
        $sql_update = "UPDATE Appointments SET status = 'Scheduled' WHERE appointment_id = :id";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute(['id' => $appointment_id]);

        // ============================================================
        // [MỚI] GỬI PUSHER CHO KHÁCH HÀNG (Realtime)
        // ============================================================
        require __DIR__ . '/../vendor/autoload.php'; // Đảm bảo đường dẫn đúng

        $options = array('cluster' => 'ap1', 'useTLS' => true);
        $pusher = new Pusher\Pusher(
            '18b40fb67053da5ad353', // KEY CỦA BẠN
            'f161fb27583a8016c4dc', // SECRET CỦA BẠN
            '2090933',              // APP ID CỦA BẠN
            $options
        );

        // Gửi data gồm ID bệnh nhân để client tự lọc (chỉ hiện cho đúng người)
        $data_pusher = [
            'patient_id' => $data['patient_id'] ?? 0, // ID bệnh nhân lấy từ query select ở trên
            'message' => "Lịch hẹn #$appointment_id của bạn đã được xác nhận!",
            'type' => 'confirmed'
        ];

        // Bắn sự kiện 'cap-nhat-trang-thai'
        $pusher->trigger('phong-kham', 'cap-nhat-trang-thai', $data_pusher);
        // ============================================================

        $msg = "Đã xác nhận lịch hẹn #$appointment_id thành công!";
        header("Location: receptionist_dashboard.php?message=" . urlencode($msg));
        exit;

    } catch (Exception $e) {
        // Nếu Pusher lỗi thì vẫn cho qua, chỉ báo lỗi log
        die("Lỗi: " . $e->getMessage());
    }
}
?>