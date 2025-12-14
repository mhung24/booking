<?php
// ================= DEBUG =================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =========================================

require_once 'config/connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

global $pdo;
$message = '';
$error_message = '';

// Lấy thông báo từ URL (sau khi xử lý thành công)
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

// ⚠️ KIỂM TRA QUYỀN TRUY CẬP & LẤY TÊN
$receptionist_name = 'Lễ tân';
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Receptionist') {
    // Giả định bạn lưu Tên Lễ tân vào session khi đăng nhập
    $receptionist_name = $_SESSION['receptionist_name'] ?? 'Lễ tân';
} else {
    // Nếu không phải lễ tân, chuyển hướng về trang đăng nhập
    // header('Location: login.php'); 
    // exit;
}


// =========================================================================
//                             KHỐI 1: XỬ LÝ HÀNH ĐỘNG HỦY
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $appointment_id = (int) ($_POST['appointment_id'] ?? 0);
    // $cancel_reason = $_POST['cancel_reason'] ?? ''; // Lưu lý do hủy nếu cần

    if ($appointment_id > 0) {
        try {
            // Chỉ hủy nếu trạng thái là Pending
            $stmt = $pdo->prepare("UPDATE Appointments SET status = 'Cancelled' WHERE appointment_id = :id AND status = 'Pending'");
            $stmt->execute(['id' => $appointment_id]);

            if ($stmt->rowCount()) {
                $message = "Đã cập nhật trạng thái lịch hẹn #{$appointment_id} thành **Cancelled** thành công!";
            } else {
                $error_message = "Lịch hẹn đã được xử lý (Xác nhận/Hủy) trước đó, không thể hủy.";
            }
        } catch (PDOException $e) {
            $error_message = "Lỗi khi cập nhật database: " . $e->getMessage();
        }
        // Tránh gửi lại form sau khi xử lý
        header('Location: receptionist_dashboard.php?message=' . urlencode($message));
        exit;
    }
}


// =========================================================================
//                         KHỐI 2: XỬ LÝ WALK-IN CHECK-IN
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['walkin_checkin'])) {

    // 1. Lấy dữ liệu
    $patient_name = trim($_POST['patient_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $doctor_id = (int) ($_POST['doctor_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? 'Khám trực tiếp');
    $paid_amount = (float) ($_POST['paid_amount'] ?? 0);
    $is_emergency = isset($_POST['is_emergency']) ? 1 : 0;

    $gender = $_POST['gender'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $address = trim($_POST['address'] ?? '');
    $bhyt_code = trim($_POST['bhyt_code'] ?? '');

    $default_pass_hash = password_hash($phone_number, PASSWORD_DEFAULT);
    $default_email = "walkin_" . time() . "@hospital.com";

    if (empty($patient_name) || empty($phone_number) || $doctor_id === 0 || empty($date_of_birth) || empty($gender) || empty($address)) {
        $error_message = 'Vui lòng điền đầy đủ các thông tin bắt buộc (Họ tên, SĐT, Bác sĩ, Ngày sinh, Giới tính, Địa chỉ).';
    } else {
        try {
            $pdo->beginTransaction();

            // 2. TẠO HỒ SƠ BỆNH NHÂN MỚI
            $sql_insert_patient = "
                INSERT INTO Patients (full_name, email, phone_number, password_hash, gender, date_of_birth, address, bhyt_code) 
                VALUES (:name, :email, :phone, :pass, :gender, :dob, :address, :bhyt)
            ";
            $stmt_patient = $pdo->prepare($sql_insert_patient);
            $stmt_patient->execute([
                'name' => $patient_name,
                'email' => $default_email,
                'phone' => $phone_number,
                'pass' => $default_pass_hash,
                'gender' => $gender,
                'dob' => $date_of_birth,
                'address' => $address,
                'bhyt' => $bhyt_code
            ]);

            $patient_id = $pdo->lastInsertId();

            // 3. TẠO LỊCH HẸN, XÁC NHẬN LUÔN VÀ GẮN CỜ WALK-IN/EMERGENCY
            $current_date = date('Y-m-d');
            $current_time = date('H:i:s');

            $sql_insert_appointment = "
                INSERT INTO Appointments (patient_id, doctor_id, date_schedule, time_schedule, reason_for_visit, status, paid_amount, is_walkin, is_emergency) 
                VALUES (:pid, :did, :date, :time, :reason, 'Confirmed', :paid, 1, :emergency)
            ";
            $stmt_app = $pdo->prepare($sql_insert_appointment);
            $stmt_app->execute([
                'pid' => $patient_id,
                'did' => $doctor_id,
                'date' => $current_date,
                'time' => $current_time,
                'reason' => $reason,
                'paid' => $paid_amount,
                'emergency' => $is_emergency
            ]);

            $pdo->commit();
            $message = "Check-in khách **{$patient_name}** thành công! Lịch hẹn đã được xếp khám ngay lập tức.";

            // Chuyển hướng để refresh trang và hiển thị lịch hẹn mới
            header('Location: receptionist_dashboard.php?message=' . urlencode($message));
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = 'Lỗi database: Không thể Check-in. ' . $e->getMessage();
        }
    }
}


// =========================================================================
//                         KHỐI 3: TRUY VẤN DATA VÀ SẮP XẾP ƯU TIÊN
// =========================================================================

// Tải danh sách Bác sĩ và Khoa cho Modal Walk-in
try {
    $stmt_doctors = $pdo->query("
        SELECT D.doctor_id, D.full_name AS doctor_name, T.department_name 
        FROM Doctors D
        JOIN Departments T ON D.department_id = T.department_id
        ORDER BY D.full_name
    ");
    $doctors = $stmt_doctors->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Không gán vào $error_message chung để tránh làm hỏng Dashboard chính
    $doctors = [];
}

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
        WHERE A.status IN ('Pending', 'Confirmed')
        
        -- SẮP XẾP THEO ƯU TIÊN: Cấp cứu (1) > Hẹn trước (2) > Đăng ký trực tiếp (3) > Đã xác nhận (4)
        ORDER BY 
            CASE 
                WHEN A.is_emergency = 1 THEN 1            
                WHEN A.is_walkin = 0 AND A.status = 'Pending' THEN 2 
                WHEN A.is_walkin = 1 THEN 3               
                ELSE 4                                    
            END ASC,
            A.appointment_date ASC, 
            A.appointment_time ASC
    ";
    $appointments = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Lỗi truy vấn lịch hẹn: Vui lòng kiểm tra các cột is_emergency và is_walkin. Chi tiết lỗi: " . $e->getMessage();
    $appointments = [];
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Lễ Tân - Quản Lý Lịch Hẹn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --confirmed-color: #198754;
            --pending-color: #ffc107;
            --emergency-color: #dc3545;
            --walkin-color: #17a2b8;
            --bg-light: #f4f7f9;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Arial', sans-serif;
        }

        .dashboard-header {
            background-color: var(--primary-color);
            color: white;
            padding: 25px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .table-modern {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            background: white;
        }

        .table-modern thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
        }

        .table-modern tbody tr {
            border-bottom: 1px solid #dee2e6;
            transition: background-color 0.2s;
        }

        .table-modern tbody tr.is-emergency {
            background-color: #fcebeb;
            border-left: 5px solid var(--emergency-color);
        }

        .table-modern tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge-pending {
            background-color: var(--pending-color);
            color: #333;
            font-weight: 600;
        }

        .badge-confirmed {
            background-color: var(--confirmed-color);
            color: white;
            font-weight: 600;
        }

        .btn-action {
            width: 70px;
            margin-bottom: 5px;
            font-size: 0.85rem;
            padding: 4px 8px;
        }
    </style>
</head>

<body>

    <div class="dashboard-header">
        <div class="container d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-clipboard-list me-2"></i> Dashboard Lễ Tân</h1>

            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fas fa-user-circle me-1"></i> Xin chào, **<?= htmlspecialchars($receptionist_name) ?>**
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="dropdownMenuButton">
                    <li>
                        <h6 class="dropdown-header">Tài khoản Lễ tân</h6>
                    </li>
                    <li><a class="dropdown-item" href="edit_receptionist_profile.php">
                            <i class="fas fa-user-edit me-2"></i> Sửa thông tin
                        </a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                        </a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?></div>
        <?php endif; ?>

        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-success btn-lg shadow-sm" data-bs-toggle="modal"
                data-bs-target="#walkinCheckinModal">
                <i class="fas fa-hospital-user me-2"></i> KHÁCH ĐĂNG KÝ TRỰC TIẾP (WALK-IN)
            </button>
        </div>

        <div class="card mb-4 table-modern">
            <div class="card-header bg-white border-bottom p-3">
                <h5 class="mb-0 text-primary"><i class="far fa-calendar-check me-2"></i> Danh sách Lịch hẹn Đang chờ
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($appointments)): ?>
                    <div class="alert alert-info text-center m-3">
                        <i class="fas fa-info-circle me-2"></i> Hiện không có lịch hẹn nào đang chờ xử lý hoặc đã xác nhận.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">ID</th>
                                    <th style="width: 10%;">Ưu tiên</th>
                                    <th style="width: 18%;">Khách hàng</th>
                                    <th style="width: 18%;">Bác sĩ/Khoa</th>
                                    <th style="width: 15%;">Ngày Giờ</th>
                                    <th>Lý do</th>
                                    <th style="width: 10%;">Trạng thái</th>
                                    <th style="width: 15%;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $app): ?>
                                    <tr class="<?= $app['is_emergency'] ? 'is-emergency' : '' ?>">
                                        <td class="fw-bold text-primary">#<?= $app['appointment_id'] ?></td>
                                        <td>
                                            <?php if ($app['is_emergency']): ?>
                                                <span class="badge bg-danger"><i class="fas fa-heartbeat"></i> CẤP CỨU</span>
                                            <?php elseif ($app['is_walkin']): ?>
                                                <span class="badge bg-info"><i class="fas fa-walking"></i> TRỰC TIẾP</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-clock"></i> HẸN TRƯỚC</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($app['patient_name']) ?></strong><br>
                                            <span class="text-muted small"><?= htmlspecialchars($app['phone_number']) ?></span>
                                        </td>
                                        <td>
                                            <span class="fw-bold"><?= htmlspecialchars($app['doctor_name']) ?></span><br>
                                            <span
                                                class="badge bg-secondary"><?= htmlspecialchars($app['department_name']) ?></span>
                                        </td>
                                        <td>
                                            <strong
                                                class="text-success"><?= date('d/m/Y', strtotime($app['appointment_date'])) ?></strong><br>
                                            <span class="small text-muted"><?= substr($app['appointment_time'], 0, 5) ?></span>
                                        </td>
                                        <td>
                                            <span class="text-truncate d-inline-block"
                                                style="max-width: 150px;"><?= htmlspecialchars($app['reason_for_visit']) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($app['status'] === 'Pending'): ?>
                                                <span class="badge badge-pending">Chờ Xử lý</span>
                                            <?php elseif ($app['status'] === 'Confirmed'): ?>
                                                <span class="badge badge-confirmed">Đã Xác nhận</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= $app['status'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="d-flex flex-column">

                                            <a href="confirm_payment.php?id=<?= $app['appointment_id'] ?>"
                                                class="btn btn-success btn-sm btn-action mb-1" title="Thanh toán & Xác nhận"
                                                <?= $app['status'] !== 'Pending' ? 'disabled' : '' ?>>
                                                <i class="fas fa-check"></i> XN
                                            </a>

                                            <button type="button" class="btn btn-danger btn-sm btn-action mb-1"
                                                data-bs-toggle="modal" data-bs-target="#cancelModal"
                                                data-appointment-id="<?= $app['appointment_id'] ?>"
                                                data-patient-name="<?= htmlspecialchars($app['patient_name']) ?>"
                                                <?= $app['status'] !== 'Pending' ? 'disabled' : '' ?>>
                                                <i class="fas fa-times"></i> Hủy
                                            </button>

                                            <button type="button" class="btn btn-info btn-sm btn-action" data-bs-toggle="modal"
                                                data-bs-target="#patientProfileModal"
                                                data-patient-id="<?= $app['patient_id'] ?>" title="Cập nhật BHYT, Địa chỉ">
                                                <i class="fas fa-edit"></i> Hồ sơ
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="modal fade" id="walkinCheckinModal" tabindex="-1" aria-labelledby="walkinCheckinModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="walkinCheckinModalLabel"><i class="fas fa-user-plus me-2"></i> Đăng ký &
                        Check-in Khách Vãng Lai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <h6 class="text-success fw-bold"><i class="fas fa-id-card me-1"></i> Thông tin Cá nhân (Hồ sơ
                            mới)</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="patient_name" class="form-label">Họ tên Khách hàng (*)</label>
                                <input type="text" class="form-control" id="patient_name" name="patient_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">Số điện thoại (*)</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                            </div>
                            <div class="col-md-4">
                                <label for="date_of_birth" class="form-label">Ngày sinh (*)</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Giới tính (*)</label>
                                <div class="mt-1">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender"
                                            id="walkin_gender_male" value="Male" required>
                                        <label class="form-check-label" for="walkin_gender_male">Nam</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender"
                                            id="walkin_gender_female" value="Female">
                                        <label class="form-check-label" for="walkin_gender_female">Nữ</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="bhyt_code" class="form-label">Mã Thẻ BHYT</label>
                                <input type="text" class="form-control" id="bhyt_code" name="bhyt_code"
                                    placeholder="Không bắt buộc">
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Địa chỉ (*)</label>
                                <textarea class="form-control" id="address" name="address" rows="1" required></textarea>
                            </div>
                        </div>

                        <hr>

                        <h6 class="text-success fw-bold"><i class="fas fa-stethoscope me-1"></i> Thông tin Khám & Thanh
                            toán</h6>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="doctor_id" class="form-label">Bác sĩ và Chuyên khoa (*)</label>
                                <select class="form-select" id="doctor_id" name="doctor_id" required>
                                    <option value="">-- Chọn Bác sĩ --</option>
                                    <?php foreach ($doctors as $doc): ?>
                                        <option value="<?= htmlspecialchars($doc['doctor_id']) ?>">
                                            <?= htmlspecialchars($doc['doctor_name']) ?>
                                            (<?= htmlspecialchars($doc['department_name']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="paid_amount" class="form-label">Số tiền đã Thanh toán (VNĐ) (*)</label>
                                <input type="number" class="form-control" id="paid_amount" name="paid_amount" min="0"
                                    placeholder="0" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="reason" class="form-label">Lý do Khám</label>
                                <input type="text" class="form-control" id="reason" name="reason"
                                    value="Khám trực tiếp">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="is_emergency"
                                        name="is_emergency">
                                    <label class="form-check-label text-danger fw-bold" for="is_emergency">
                                        <i class="fas fa-exclamation-circle me-1"></i> Đánh dấu là Trường hợp CẤP CỨU
                                        (Ưu tiên cao nhất)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" name="walkin_checkin" class="btn btn-success">
                            <i class="fas fa-check-double me-1"></i> CHECK-IN & XÁC NHẬN NGAY
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="cancelModalLabel"><i class="fas fa-exclamation-triangle"></i> Xác nhận
                        Hủy Lịch hẹn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn hủy lịch hẹn: <strong id="modal-patient-name"
                            class="text-primary"></strong>?</p>
                    <p class="text-danger small">Hành động này không thể hoàn tác.</p>

                    <form id="cancelForm" method="POST">
                        <input type="hidden" name="appointment_id" id="modal-appointment-id" value="">
                        <input type="hidden" name="action" value="cancel">
                        <div class="mb-3">
                            <label for="cancel_reason" class="form-label">Lý do Hủy (Không bắt buộc)</label>
                            <input type="text" class="form-control" id="cancel_reason" name="cancel_reason">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" form="cancelForm" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> XÁC NHẬN HỦY
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="patientProfileModal" tabindex="-1" aria-labelledby="patientProfileModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="patientProfileModalLabel"><i class="fas fa-edit me-2"></i> Cập nhật Hồ
                        sơ Bệnh nhân</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="patientProfileContent">
                    <div class="text-center p-5">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <p class="mt-2">Đang tải thông tin hồ sơ...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cancelModal = document.getElementById('cancelModal');
            if (cancelModal) {
                cancelModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const appointmentId = button.getAttribute('data-appointment-id');
                    const patientName = button.getAttribute('data-patient-name');

                    const modalAppointmentId = cancelModal.querySelector('#modal-appointment-id');
                    const modalPatientName = cancelModal.querySelector('#modal-patient-name');

                    modalAppointmentId.value = appointmentId;
                    modalPatientName.textContent = patientName;
                });
            }

            const profileModal = document.getElementById('patientProfileModal');
            if (profileModal) {
                profileModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const patientId = button.getAttribute('data-patient-id');
                    const modalContent = document.getElementById('patientProfileContent');

                    // Hiển thị trạng thái tải
                    modalContent.innerHTML = `
                <div class="text-center p-5">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2">Đang tải thông tin hồ sơ...</p>
                </div>
            `;

                    // Thực hiện tải nội dung Form từ edit_patient.php
                    fetch(`edit_patient.php?id=${patientId}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Lỗi tải dữ liệu');
                            }
                            return response.text();
                        })
                        .then(html => {
                            // Chèn nội dung Form đã tải vào Modal
                            modalContent.innerHTML = html;

                            // Thêm class cho Form (nếu cần, để Form không bị style lớn như trang độc lập)
                            const formContainer = modalContent.querySelector('.form-container');
                            if (formContainer) {
                                formContainer.classList.remove('form-container');
                                formContainer.style.margin = '0'; // Loại bỏ margin
                                formContainer.style.boxShadow = 'none'; // Loại bỏ box-shadow
                                formContainer.style.padding = '0'; // Loại bỏ padding
                            }
                        })
                        .catch(error => {
                            modalContent.innerHTML = `<div class="alert alert-danger">Không thể tải hồ sơ. Lỗi: ${error.message}</div>`;
                        });
                });

                // Xóa nội dung Modal khi đóng để tránh cache form của bệnh nhân trước đó
                profileModal.addEventListener('hidden.bs.modal', function () {
                    document.getElementById('patientProfileContent').innerHTML = '';
                });
            }
        });
    </script>
</body>

</html>