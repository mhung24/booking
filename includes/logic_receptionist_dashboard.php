<?php
// FILE: includes/logic_receptionist_dashboard.php

// 1. Cấu hình & Check lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

global $pdo;
$message = '';
$error_message = '';

// 2. Auth Check (Bảo mật)
$receptionist_name = 'Lễ tân';
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Receptionist') {
    $receptionist_name = $_SESSION['receptionist_name'] ?? 'Lễ tân';
} else {
    header("Location: login.php");
    exit;
}

// 3. Xử lý thông báo từ URL
if (isset($_GET['msg'])) {
    $msg_type = $_GET['msg'];
    if ($msg_type === 'cancel_success') {
        $message = "Đã hủy lịch hẹn thành công.";
    } elseif ($msg_type === 'cancel_error') {
        $error_message = "Không thể hủy (Lỗi hệ thống hoặc sai trạng thái).";
    } elseif ($msg_type === 'update_success') {
        $message = "Đã cập nhật và gửi hồ sơ sang bác sĩ thành công.";
    } elseif ($msg_type === 'confirm_success') {
        $message = "Đã xác nhận lịch hẹn thành công.";
    } elseif ($msg_type === 'error') {
        $error_message = isset($_GET['detail']) ? urldecode($_GET['detail']) : "Đã có lỗi xảy ra.";
    }
}
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8');
}

// Hàm hỗ trợ
function get_bhyt_coverage_rate($bhyt_code)
{
    return !empty($bhyt_code) ? 0.80 : 0.00;
}

// 4. XỬ LÝ AJAX LOAD PROFILE (Để hiện popup)
if (isset($_GET['action']) && $_GET['action'] === 'load_profile' && isset($_GET['id'])) {
    $patient_id = (int) $_GET['id'];

    // Load Services
    try {
        $stmt_services = $pdo->query("SELECT service_id, service_name, price FROM Services ORDER BY service_name");
        $services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Lỗi load services");
    }

    // Load Patient
    $stmt_p = $pdo->prepare("SELECT * FROM Patients WHERE patient_id = :id");
    $stmt_p->execute(['id' => $patient_id]);
    $patient = $stmt_p->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo '<div class="alert alert-danger">Không tìm thấy bệnh nhân.</div>';
        exit;
    }

    // Load Appointment (Lấy lịch hẹn gần nhất chưa hoàn thành)
    // Lấy bất kỳ trạng thái nào chưa xong để lễ tân có thể sửa nếu cần
    $stmt_app = $pdo->prepare("SELECT appointment_id, service_id, paid_amount, status FROM Appointments WHERE patient_id = :pid AND status NOT IN ('Completed', 'Cancelled') ORDER BY appointment_date DESC LIMIT 1");
    $stmt_app->execute(['pid' => $patient_id]);
    $current_app = $stmt_app->fetch(PDO::FETCH_ASSOC);

    $bhyt_rate = get_bhyt_coverage_rate($patient['bhyt_code'] ?? '');

    // Render HTML Form
    ob_start();
    ?>
    <div class="p-3">
        <form method="POST" action="receptionist_dashboard.php">
            <input type="hidden" name="update_patient" value="1">
            <input type="hidden" name="patient_id_hidden" value="<?= $patient_id ?>">
            <input type="hidden" name="appointment_id" value="<?= $current_app['appointment_id'] ?? 0 ?>">

            <div class="card mb-3 border-info">
                <div class="card-header bg-info text-white">Thông tin Cá nhân</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Họ tên</label>
                            <input type="text" class="form-control" name="full_name"
                                value="<?= htmlspecialchars($patient['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Số điện thoại</label>
                            <input type="text" class="form-control" name="phone_number"
                                value="<?= htmlspecialchars($patient['phone_number']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mã BHYT</label>
                            <input type="text" class="form-control" name="bhyt_code"
                                value="<?= htmlspecialchars($patient['bhyt_code'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Địa chỉ</label>
                            <input type="text" class="form-control" name="address"
                                value="<?= htmlspecialchars($patient['address'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 border-success">
                <div class="card-header bg-success text-white">Dịch vụ & Thanh toán</div>
                <div class="card-body">
                    <input type="hidden" id="bhyt_rate_edit" value="<?= $bhyt_rate ?>">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Chọn Dịch vụ</label>
                            <select class="form-select" id="service_id_edit" name="service_id">
                                <option value="" data-price="0">-- Chọn --</option>
                                <?php foreach ($services as $sv): ?>
                                    <option value="<?= $sv['service_id'] ?>" data-price="<?= $sv['price'] ?>"
                                        <?= (isset($current_app['service_id']) && $current_app['service_id'] == $sv['service_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sv['service_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Thực thu (VNĐ)</label>
                            <input type="text" class="form-control fw-bold text-danger" id="total_to_pay_display" readonly
                                value="0">
                            <input type="hidden" name="paid_amount" id="paid_amount_edit"
                                value="<?= $current_app['paid_amount'] ?? 0 ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary" id="btn-save-update">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
    <?php
    echo ob_get_clean();
    exit;
}

// 5. XỬ LÝ POST (HỦY & UPDATE & GỬI BÁC SĨ)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- 5.1: Xử lý Hủy lịch ---
    if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
        $app_id = (int) $_POST['appointment_id'];
        try {
            // Chỉ hủy nếu chưa Hoàn thành và chưa Hủy
            $stmt = $pdo->prepare("UPDATE Appointments SET status = 'Cancelled' WHERE appointment_id = :id AND status NOT IN ('Completed', 'Cancelled')");
            $stmt->execute(['id' => $app_id]);

            $redirect_msg = ($stmt->rowCount() > 0) ? 'cancel_success' : 'cancel_error';
            header("Location: receptionist_dashboard.php?msg=$redirect_msg");
            exit;
        } catch (PDOException $e) {
            header("Location: receptionist_dashboard.php?msg=cancel_error");
            exit;
        }
    }

    // --- 5.2: Cập nhật Hồ sơ & Gửi cho Bác sĩ ---
    if (isset($_POST['update_patient'])) {
        $p_id = (int) $_POST['patient_id_hidden'];
        $app_id = (int) $_POST['appointment_id'];

        try {
            $pdo->beginTransaction();

            // 1. Update thông tin bệnh nhân
            $stmt_p = $pdo->prepare("UPDATE Patients SET full_name = :name, phone_number = :phone, bhyt_code = :bhyt, address = :addr WHERE patient_id = :id");
            $stmt_p->execute([
                'name' => $_POST['full_name'],
                'phone' => $_POST['phone_number'],
                'bhyt' => $_POST['bhyt_code'],
                'addr' => $_POST['address'],
                'id' => $p_id
            ]);

            // 2. Update Lịch hẹn -> Chuyển sang 'Waiting' & Set thời gian xếp hàng
            if ($app_id > 0) {
                $sql_a = "
                    UPDATE Appointments 
                    SET service_id = :sid, 
                        paid_amount = :paid,
                        status = 'Waiting',       -- Chuyển trạng thái để Bác sĩ thấy
                        queued_at = NOW()         -- Lưu thời gian để xếp hàng
                    WHERE appointment_id = :aid
                ";
                $stmt_a = $pdo->prepare($sql_a);
                $stmt_a->execute([
                    'sid' => $_POST['service_id'],
                    'paid' => $_POST['paid_amount'],
                    'aid' => $app_id
                ]);

                // 3. Bắn Pusher (Try-catch riêng để không chết luồng chính)
                try {
                    $autoload_path = __DIR__ . '/../vendor/autoload.php';
                    if (file_exists($autoload_path)) {
                        require_once $autoload_path;
                        $options = ['cluster' => 'ap1', 'useTLS' => true];
                        $pusher = new Pusher\Pusher('18b40fb67053da5ad353', 'f161fb27583a8016c4dc', '2090933', $options);

                        $pusher->trigger('phong-kham', 'bac-si-nhan-benh-nhan', [
                            'message' => 'Có bệnh nhân mới đang chờ khám!',
                            'patient_name' => $_POST['full_name']
                        ]);
                    }
                } catch (Exception $e_pusher) {
                    // Log lỗi pusher nếu cần, không chặn code
                }
            }

            $pdo->commit();
            header("Location: receptionist_dashboard.php?msg=update_success");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = urlencode("Lỗi cập nhật: " . $e->getMessage());
            header("Location: receptionist_dashboard.php?msg=error&detail=$error_msg");
            exit;
        }
    }
}

// 6. LẤY DANH SÁCH LỊCH HẸN (TÌM KIẾM & LỌC)
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

try {
    $sql = "
        SELECT 
            A.appointment_id, A.patient_id, A.appointment_date, A.appointment_time, A.reason_for_visit, A.status, A.is_emergency, A.is_walkin,
            P.full_name AS patient_name, P.phone_number,
            D.full_name AS doctor_name, T.department_name
        FROM Appointments A
        JOIN Patients P ON A.patient_id = P.patient_id
        JOIN Doctors D ON A.doctor_id = D.doctor_id
        JOIN Departments T ON D.department_id = T.department_id
        WHERE 1=1 
    ";

    $params = [];

    // Tìm kiếm
    if (!empty($keyword)) {
        $sql .= " AND (P.full_name LIKE :keyword OR P.phone_number LIKE :keyword)";
        $params['keyword'] = "%$keyword%";
    }

    // Lọc trạng thái
    if (!empty($status_filter)) {
        $sql .= " AND A.status = :status";
        $params['status'] = $status_filter;
    }

    // Sắp xếp: Cấp cứu -> Chờ -> Chờ khám -> Mới nhất
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

} catch (Exception $e) {
    $appointments = [];
    $error_message = "Lỗi tải dữ liệu: " . $e->getMessage();
}
?>