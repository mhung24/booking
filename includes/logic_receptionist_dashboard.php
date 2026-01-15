<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/connect.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

global $pdo;
$message = '';
$error_message = '';

// 1. Auth Check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Receptionist') {
    header("Location: login.php");
    exit;
}
$receptionist_name = $_SESSION['receptionist_name'] ?? 'Lễ tân';

// 2. Xử lý thông báo từ URL
if (isset($_GET['msg'])) {
    $m = $_GET['msg'];
    if ($m === 'cancel_success')
        $message = "Đã hủy lịch hẹn thành công.";
    if ($m === 'update_success')
        $message = "Hồ sơ đã được gửi tới bác sĩ.";
    if ($m === 'confirm_success')
        $message = "Đã xác nhận lịch hẹn.";
    if ($m === 'error')
        $error_message = "Đã có lỗi xảy ra trong quá trình xử lý.";
}

// 3. XỬ LÝ AJAX: Load thông tin nhanh để hiện trong Modal
if (isset($_GET['action']) && $_GET['action'] === 'load_profile' && isset($_GET['id'])) {
    $patient_id = (int) $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM Patients WHERE patient_id = ?");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo '<div class="p-4 text-center text-danger">Không tìm thấy hồ sơ.</div>';
        exit;
    }

    // Lấy lịch hẹn mới nhất của bệnh nhân này mà chưa khám
    $stmt_app = $pdo->prepare("SELECT appointment_id, doctor_id, appointment_date FROM Appointments WHERE patient_id = ? AND status IN ('Pending', 'Scheduled') ORDER BY appointment_date DESC LIMIT 1");
    $stmt_app->execute([$patient_id]);
    $app = $stmt_app->fetch(PDO::FETCH_ASSOC);

    ob_start(); ?>
    <div class="p-3">
        <form method="POST" action="receptionist_dashboard.php">
            <input type="hidden" name="action" value="quick_checkin">
            <input type="hidden" name="patient_id" value="<?= $patient['patient_id'] ?>">
            <input type="hidden" name="appointment_id" value="<?= $app['appointment_id'] ?? 0 ?>">

            <div class="mb-3">
                <label class="form-label small fw-bold">Họ và Tên</label>
                <input type="text" class="form-control bg-white" name="full_name"
                    value="<?= htmlspecialchars($patient['full_name']) ?>" required>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Số điện thoại</label>
                    <input type="text" class="form-control bg-white" name="phone"
                        value="<?= htmlspecialchars($patient['phone_number']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Mã BHYT (nếu có)</label>
                    <input type="text" class="form-control bg-white" name="bhyt"
                        value="<?= htmlspecialchars($patient['bhyt_code'] ?? '') ?>">
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm rounded-3">
                <i class="fas fa-info-circle me-2"></i>
                Sau khi bấm <strong>"Xác nhận tiếp nhận"</strong>, bệnh nhân sẽ được đưa vào hàng đợi của Bác sĩ.
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm">XÁC NHẬN TIẾP
                NHẬN</button>
        </form>
    </div>
    <?php
    echo ob_get_clean();
    exit;
}

// 4. XỬ LÝ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 4.1 Hủy lịch
    if ($action === 'cancel') {
        $aid = (int) $_POST['appointment_id'];
        $stmt = $pdo->prepare("UPDATE Appointments SET status = 'Cancelled' WHERE appointment_id = ? AND status NOT IN ('Completed', 'Cancelled')");
        $stmt->execute([$aid]);
        header("Location: receptionist_dashboard.php?msg=cancel_success");
        exit;
    }

    // 4.2 Tiếp nhận nhanh từ Modal Dashboard
    if ($action === 'quick_checkin') {
        try {
            $pdo->beginTransaction();
            $pid = (int) $_POST['patient_id'];
            $aid = (int) $_POST['appointment_id'];

            // Cập nhật lại thông tin bệnh nhân nếu có thay đổi (SĐT, BHYT)
            $stmt_p = $pdo->prepare("UPDATE Patients SET full_name = ?, phone_number = ?, bhyt_code = ? WHERE patient_id = ?");
            $stmt_p->execute([$_POST['full_name'], $_POST['phone'], $_POST['bhyt'], $pid]);

            if ($aid > 0) {
                // Nếu đã có lịch hẹn, chuyển trạng thái sang Waiting (Chờ khám)
                $stmt_a = $pdo->prepare("UPDATE Appointments SET status = 'Waiting', queued_at = NOW() WHERE appointment_id = ?");
                $stmt_a->execute([$aid]);
            } else {
                // Nếu chưa có lịch hẹn (vãng lai), lễ tân cần chọn BS (Yêu cầu qua trang Patients để tiếp nhận chuẩn hơn)
                // Ở đây mặc định gán một BS (tùy cấu trúc phòng khám của bạn) hoặc báo lỗi
                throw new Exception("Bệnh nhân vãng lai vui lòng tiếp nhận tại mục Hồ sơ.");
            }

            // Bắn Pusher cho Bác sĩ
            try {
                $autoload = __DIR__ . '/../vendor/autoload.php';
                if (file_exists($autoload)) {
                    require_once $autoload;
                    $pusher = new Pusher\Pusher('18b40fb67053da5ad353', 'f161fb27583a8016c4dc', '2090933', ['cluster' => 'ap1', 'useTLS' => true]);
                    $pusher->trigger('phong-kham', 'bac-si-nhan-benh-nhan', ['message' => 'Có bệnh nhân mới vào hàng đợi!']);
                }
            } catch (Exception $e) {
            }

            $pdo->commit();
            header("Location: receptionist_dashboard.php?msg=update_success");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: receptionist_dashboard.php?msg=error&detail=" . urlencode($e->getMessage()));
            exit;
        }
    }
}

// 5. LẤY DANH SÁCH LỊCH HẸN ĐỂ HIỂN THỊ
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "
    SELECT 
        A.appointment_id, A.patient_id, A.appointment_date, A.appointment_time, A.status, A.is_emergency, A.is_walkin,
        P.full_name AS patient_name, P.phone_number,
        D.full_name AS doctor_name, T.department_name
    FROM Appointments A
    JOIN Patients P ON A.patient_id = P.patient_id
    JOIN Doctors D ON A.doctor_id = D.doctor_id
    JOIN Departments T ON D.department_id = T.department_id
    WHERE 1=1 
";

$params = [];
if (!empty($keyword)) {
    $sql .= " AND (P.full_name LIKE :kw OR P.phone_number LIKE :kw)";
    $params['kw'] = "%$keyword%";
}
if (!empty($status_filter)) {
    $sql .= " AND A.status = :st";
    $params['st'] = $status_filter;
}

// Sắp xếp ưu tiên: Cấp cứu -> Chờ xác nhận -> Chờ khám -> Khác
$sql .= " 
    ORDER BY 
        CASE 
            WHEN A.is_emergency = 1 AND A.status != 'Completed' THEN 1 
            WHEN A.status = 'Pending' THEN 2
            WHEN A.status = 'Scheduled' THEN 3
            WHEN A.status = 'Waiting' THEN 4
            ELSE 5
        END,
        A.appointment_date DESC, A.appointment_time ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);