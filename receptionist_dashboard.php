<?php
// ================= DEBUG =================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =========================================

require_once 'config/connect.php';

// Sửa lỗi cú pháp: Dùng session_status() để kiểm tra nếu session chưa khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

global $pdo;
$message = '';
$error_message = '';
// Lấy thông báo từ URL (sau khi xử lý thành công)
if (isset($_GET['message']) && $_GET['message'] !== '') {
    // Đảm bảo sử dụng ENT_QUOTES và UTF-8 để tránh lỗi ký tự
    $message = htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8');
}

// ⚠️ KIỂM TRA QUYỀN TRUY CẬP & LẤY TÊN
$receptionist_name = 'Lễ tân';

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Receptionist') {
    $receptionist_name = $_SESSION['receptionist_name'] ?? 'Lễ tân';
}

// --- HÀM GIẢ ĐỊNH TÍNH TỈ LỆ KHẤU TRỪ BHYT ---
function get_bhyt_coverage_rate($bhyt_code)
{
    // Logic giả định: Nếu có mã BHYT thì chi trả 80%
    if (!empty($bhyt_code)) {
        return 0.80;
    }
    return 0.00;
}
// ---------------------------------------------


// =========================================================================
//                  KHỐI 1: XỬ LÝ AJAX LOAD PROFILE 
// (Phải đặt trước phần HTML để ngăn lỗi header)
// =========================================================================

/**
 * Hàm render nội dung HTML cho Modal Cập nhật Hồ sơ.
 */
function getPatientProfileHtml($patient_id, $pdo, $services)
{
    ob_start();

    try {
        // 1. Lấy thông tin bệnh nhân
        $stmt_patient = $pdo->prepare(
            "SELECT * FROM Patients WHERE patient_id = :id"
        );
        $stmt_patient->execute(['id' => $patient_id]);
        $patient = $stmt_patient->fetch(PDO::FETCH_ASSOC);

        if (!$patient) {
            return '<div class="alert alert-danger">Không tìm thấy bệnh nhân.</div>';
        }

        $patient_dob = $patient['date_of_birth']
            ? date('Y-m-d', strtotime($patient['date_of_birth']))
            : '';

        $bhyt_rate = get_bhyt_coverage_rate($patient['bhyt_code']);

        // 2. Lấy lịch hẹn gần nhất (để lấy service_id và paid_amount hiện tại)
        $stmt_app = $pdo->prepare("
            SELECT appointment_id, service_id, paid_amount 
            FROM Appointments 
            WHERE patient_id = :pid AND status IN ('Pending', 'Confirmed') 
            ORDER BY appointment_date DESC, appointment_time DESC 
            LIMIT 1
        ");
        $stmt_app->execute(['pid' => $patient_id]);
        $current_appointment = $stmt_app->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        return '<div class="alert alert-danger">Lỗi truy vấn hồ sơ: ' . $e->getMessage() . '</div>';
    }

    // --- BẮT ĐẦU HTML FORM ---
    ?>
    <div class="p-3">
        <form method="POST">
            <input type="hidden" name="update_patient" value="1">
            <input type="hidden" name="patient_id_hidden" value="<?= htmlspecialchars($patient_id) ?>">
            <input type="hidden" name="appointment_id"
                value="<?= htmlspecialchars($current_appointment['appointment_id'] ?? 0) ?>">
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white fw-bold"><i class="fas fa-user me-1"></i> Thông tin Cá nhân</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="full_name" class="form-label small fw-bold">Họ tên (*)</label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                value="<?= htmlspecialchars($patient['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone_number" class="form-label small fw-bold">Số điện thoại (*)</label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number"
                                value="<?= htmlspecialchars($patient['phone_number']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="date_of_birth" class="form-label small fw-bold">Ngày sinh (*)</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                value="<?= $patient_dob ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Giới tính (*)</label>
                            <div class="mt-1">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="gender_male" value="Male"
                                        <?= ($patient['gender'] === 'Male') ? 'checked' : '' ?> required>
                                    <label class="form-check-label small" for="gender_male">Nam</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="gender_female"
                                        value="Female" <?= ($patient['gender'] === 'Female') ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="gender_female">Nữ</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="bhyt_code" class="form-label small fw-bold">Mã Thẻ BHYT</label>
                            <input type="text" class="form-control" id="bhyt_code" name="bhyt_code"
                                value="<?= htmlspecialchars($patient['bhyt_code'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label small fw-bold">Địa chỉ (*)</label>
                            <textarea class="form-control" id="address" name="address" rows="1"
                                required><?= htmlspecialchars($patient['address']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white fw-bold"><i class="fas fa-money-check-alt me-1"></i> Dịch vụ &
                    Thanh toán</div>
                <div class="card-body">
                    <input type="hidden" id="bhyt_rate_edit" value="<?= $bhyt_rate ?>">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="service_id_edit" class="form-label small fw-bold">Loại Bệnh/Dịch vụ (*)</label>
                            <select class="form-select" id="service_id_edit" name="service_id" required>
                                <option value="" data-price="0">-- Chọn Loại dịch vụ --</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= htmlspecialchars($service['service_id']) ?>"
                                        data-price="<?= htmlspecialchars($service['price']) ?>"
                                        <?= (isset($current_appointment['service_id']) && $current_appointment['service_id'] == $service['service_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($service['service_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Phí dịch vụ (*)</label>
                            <input type="text" class="form-control text-primary fw-bold" id="base_fee_display" readonly
                                value="0">
                        </div>
                        <input type="hidden" name="paid_amount" id="paid_amount_edit"
                            value="<?= htmlspecialchars($current_appointment['paid_amount'] ?? 0) ?>">

                        <div class="col-12 mt-4">
                            <div class="alert alert-warning p-3 border-0 shadow-sm">
                                <div class="row">
                                    <div class="col-8 small">Tỷ lệ BHYT chi trả (Dựa trên Mã BHYT):</div>
                                    <div class="col-4 text-end fw-bold text-danger" id="bhyt_coverage_display">
                                        <?= ($bhyt_rate * 100) . '%' ?>
                                    </div>
                                </div>
                                <div class="row mt-2 border-top pt-2">
                                    <div class="col-8 fw-bold">TỔNG KHÁCH HÀNG CẦN THANH TOÁN:</div>
                                    <div class="col-4 text-end fw-bold text-success fs-5" id="total_to_pay_display">0 VNĐ
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-info btn-lg">
                    <i class="fas fa-save me-1"></i> Lưu Cập nhật Hồ sơ
                </button>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}


if (isset($_GET['action']) && $_GET['action'] === 'load_profile' && isset($_GET['id'])) {
    $patient_id_to_load = (int) $_GET['id'];

    // Đảm bảo $services đã được tải nếu nó là biến toàn cục cần thiết
    // (Ta sẽ tải $services ngay bên dưới khối này, nhưng để an toàn, ta tải lại nếu cần)
    if (!isset($services)) {
        try {
            $stmt_services = $pdo->query("SELECT service_id, service_name, price FROM Services ORDER BY service_name");
            $services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Lỗi tải danh sách Dịch vụ.</div>';
            exit;
        }
    }

    if ($patient_id_to_load > 0) {
        echo getPatientProfileHtml($patient_id_to_load, $pdo, $services);
    } else {
        echo '<div class="alert alert-danger">ID bệnh nhân không hợp lệ.</div>';
    }
    exit; // LỆNH QUAN TRỌNG: Ngăn việc tải toàn bộ trang HTML
}


// =========================================================================
//                  KHỐI 2: XỬ LÝ FORM CẬP NHẬT/HỦY (POST)
// =========================================================================

// --- XỬ LÝ HÀNH ĐỘNG HỦY ---
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'cancel'
) {
    $appointment_id = (int) ($_POST['appointment_id'] ?? 0);

    if ($appointment_id > 0) {
        try {
            $stmt = $pdo->prepare(
                "UPDATE Appointments
                 SET status = 'Cancelled'
                 WHERE appointment_id = :id
                 AND status = 'Pending'"
            );

            $stmt->execute(['id' => $appointment_id]);

            if ($stmt->rowCount() > 0) {
                $message = "Đã hủy lịch hẹn #{$appointment_id} thành công.";
            } else {
                $message = "Lịch hẹn đã được xử lý trước đó hoặc không tồn tại, không thể hủy.";
            }
        } catch (PDOException $e) {
            $message = "Lỗi hệ thống, vui lòng thử lại sau.";
        }

        header(
            'Location: receptionist_dashboard.php?message='
            . urlencode($message)
        );
        exit;
    }
}


// --- XỬ LÝ CẬP NHẬT HỒ SƠ TỪ MODAL PROFILE (POST request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_patient'])) {

    $patient_id = (int) ($_POST['patient_id_hidden'] ?? 0);
    $patient_name = trim($_POST['full_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $address = trim($_POST['address'] ?? '');
    $bhyt_code = trim($_POST['bhyt_code'] ?? '');
    $paid_amount = (float) ($_POST['paid_amount'] ?? 0);
    $service_id = (int) ($_POST['service_id'] ?? 0);
    $appointment_id = (int) ($_POST['appointment_id'] ?? 0);

    if (empty($patient_name) || empty($phone_number) || empty($date_of_birth) || empty($gender) || $patient_id <= 0) {
        $error_message = 'Vui lòng điền đầy đủ các thông tin bắt buộc và kiểm tra ID bệnh nhân.';
    } else {
        try {
            $pdo->beginTransaction();

            // Cập nhật thông tin Bệnh nhân (chính)
            $sql_update_patient = "
                UPDATE Patients SET full_name = :name, phone_number = :phone, gender = :gender, date_of_birth = :dob, address = :address, bhyt_code = :bhyt
                WHERE patient_id = :id
            ";
            $stmt = $pdo->prepare($sql_update_patient);
            $stmt->execute([
                'name' => $patient_name,
                'phone' => $phone_number,
                'gender' => $gender,
                'dob' => $date_of_birth,
                'address' => $address,
                'bhyt' => $bhyt_code,
                'id' => $patient_id
            ]);

            // Cập nhật thông tin dịch vụ/phí cho LỊCH HẸN HIỆN TẠI
            if ($appointment_id > 0) {
                $sql_update_appointment = "
                    UPDATE Appointments SET
                        service_id = :sid,
                        paid_amount = :paid
                    WHERE appointment_id = :aid
                ";
                $stmt_app = $pdo->prepare($sql_update_appointment);
                $stmt_app->execute([
                    'sid' => $service_id,
                    'paid' => $paid_amount,
                    'aid' => $appointment_id
                ]);
            }

            $pdo->commit();
            $message = "Cập nhật hồ sơ bệnh nhân #{$patient_id} thành công!";
            header('Location: receptionist_dashboard.php?message=' . urlencode($message));
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = 'Lỗi cập nhật database: ' . $e->getMessage();
        }
    }
}


// =========================================================================
//                 KHỐI 3: TRUY VẤN DATA VÀ SẮP XẾP ƯU TIÊN
// =========================================================================

// Tải danh sách Bác sĩ, Khoa, Dịch vụ
try {
    $stmt_doctors = $pdo->query("
        SELECT D.doctor_id, D.full_name AS doctor_name, T.department_name
        FROM Doctors D
        JOIN Departments T ON D.department_id = T.department_id
        ORDER BY D.full_name
    ");
    $doctors = $stmt_doctors->fetchAll(PDO::FETCH_ASSOC);

    $stmt_services = $pdo->query("
        SELECT service_id, service_name, price
        FROM Services
        ORDER BY service_name
    ");
    $services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $doctors = [];
    $services = [];
    $error_message = "Lỗi tải dữ liệu cơ bản (Bác sĩ/Dịch vụ): " . $e->getMessage();
}

// Tải danh sách lịch hẹn
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
        ORDER BY
            CASE
                WHEN A.is_emergency = 1 THEN 1
                WHEN A.is_walkin = 0 AND A.status = 'Pending' THEN 2
                WHEN A.is_walkin = 1 THEN 3
                ELSE 4
            END,
            A.appointment_date ASC,
            A.appointment_time ASC
    ";

    $appointments = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $appointments = [];
    $error_message = "Lỗi truy vấn lịch hẹn: " . $e->getMessage();
}

// ======================================================================
//                              KHỐI 4: HTML
// ======================================================================
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">
    <title>Dashboard Lễ Tân - Quản Lý Lịch Hẹn</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="./css/receptionist_style.css" rel="stylesheet">
</head>

<body>

    <div class="dashboard-header">
        <div class="container d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-clipboard-list me-2"></i> Dashboard Lễ Tân</h1>

            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fas fa-user-circle me-1"></i> Xin chào,
                    **<?= htmlspecialchars($receptionist_name) ?>**
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
                <h5 class="mb-0 text-primary"><i class="far fa-calendar-check me-2"></i> Danh sách Lịch
                    hẹn Đang chờ
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($appointments)): ?>
                    <div class="alert alert-info text-center m-3">
                        <i class="fas fa-info-circle me-2"></i> Hiện không có lịch hẹn nào đang chờ
                        xử lý hoặc đã xác nhận.
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
                                        <td class="fw-bold text-primary">
                                            #<?= $app['appointment_id'] ?></td>
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
                                                <i class="fas fa-edit"></i> Hồ
                                                sơ
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
        <div class="modal-dialog">...</div>
    </div>


    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">...</div>
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
                    <div class="text-center p-5">Đang chờ tải dữ liệu...</div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // =======================================================
        // LOGIC TÍNH PHÍ (GLOBAL SCOPE)
        // =======================================================
        // Hàm này phải là GLOBAL để có thể được gọi sau khi nội dung AJAX được chèn
        window.calculateFee = function () {
            const selectService = document.getElementById('service_id_edit');
            const paidAmountInput = document.getElementById('paid_amount_edit');
            const baseFeeDisplay = document.getElementById('base_fee_display');
            const totalToPayDisplay = document.getElementById('total_to_pay_display');
            const bhytRateInput = document.getElementById('bhyt_rate_edit');

            if (!selectService || !paidAmountInput || !baseFeeDisplay || !totalToPayDisplay || !bhytRateInput) {
                // Console.log("Không tìm thấy đủ các phần tử tính phí.");
                return;
            }

            const BHYT_RATE = parseFloat(bhytRateInput.value || 0.00);
            const formatCurrency = (number) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(number);

            function runCalculation() {
                const selectedOption = selectService.options[selectService.selectedIndex];
                const baseFee = parseFloat(selectedOption.getAttribute('data-price')) || 0;

                let totalFee;

                if (BHYT_RATE > 0) {
                    const patientShareRate = 1.00 - BHYT_RATE;
                    totalFee = baseFee * patientShareRate;
                } else {
                    totalFee = baseFee;
                }

                baseFeeDisplay.value = formatCurrency(baseFee);
                totalToPayDisplay.textContent = formatCurrency(totalFee);
                // Cập nhật giá trị vào trường ẩn paid_amount
                paidAmountInput.value = totalFee.toFixed(0);
            }

            // Chạy lần đầu tiên để thiết lập giá trị ban đầu
            runCalculation();

            // Đảm bảo chỉ có một listener (nếu hàm này được gọi nhiều lần)
            selectService.removeEventListener('change', runCalculation);
            selectService.addEventListener('change', runCalculation);
        };
        // =======================================================


        // HÀM TẢI NỘI DUNG MODAL PROFILE BẰNG AJAX
        function openPatientProfile(patientId) {
            const modalContent = document.getElementById('patientProfileContent');

            modalContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-info" role="status"><span class="visually-hidden">Đang tải...</span></div><p class="mt-2">Đang tải thông tin hồ sơ...</p></div>`;

            // Lệnh gọi AJAX đến chính file này với action=load_profile
            fetch(`receptionist_dashboard.php?action=load_profile&id=${patientId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Lỗi tải dữ liệu');
                    }
                    return response.text();
                })
                .then(html => {
                    // Chèn nội dung Form đã tải vào Modal
                    modalContent.innerHTML = html;

                    // Gọi hàm tính phí sau khi nội dung đã được chèn vào DOM
                    setTimeout(() => {
                        if (typeof window.calculateFee === 'function') {
                            window.calculateFee();
                        } else {
                            console.error("Lỗi: Hàm calculateFee() chưa sẵn sàng.");
                        }
                    }, 0);
                })
                .catch(error => {
                    modalContent.innerHTML = `<div class="alert alert-danger">Không thể tải hồ sơ. Lỗi: ${error.message}</div>`;
                });
        }


        document.addEventListener('DOMContentLoaded', function () {
            // --- Xử lý Modal Hủy (Giữ nguyên) ---
            const cancelModal = document.getElementById('cancelModal');
            if (cancelModal) {
                cancelModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const appointmentId = button.getAttribute('data-appointment-id');
                    const patientName = button.getAttribute('data-patient-name');

                    // Giả định bạn có các input/element này trong cancelModal
                    const modalAppointmentId = cancelModal.querySelector('#modal-appointment-id');
                    const modalPatientName = cancelModal.querySelector('#modal-patient-name');

                    if (modalAppointmentId) {
                        modalAppointmentId.value = appointmentId;
                    }
                    if (modalPatientName) {
                        modalPatientName.textContent = patientName;
                    }
                });
            }

            // --- Xử lý Modal Profile (Kích hoạt tải dữ liệu) ---
            const profileModal = document.getElementById('patientProfileModal');
            if (profileModal) {
                profileModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    // Lấy ID từ data-patient-id (ĐÃ ĐỒNG BỘ)
                    const patientId = button.getAttribute('data-patient-id');

                    // GỌI HÀM TẢI VÀ CHÈN FORM
                    openPatientProfile(patientId);
                });

                // Xóa nội dung Modal khi đóng
                profileModal.addEventListener('hidden.bs.modal', function () {
                    document.getElementById('patientProfileContent').innerHTML = '';
                });
            }
        });
    </script>
</body>

</html>