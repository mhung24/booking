<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config/connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// === KIỂM TRA ĐĂNG NHẬP ===
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=" . $redirect_url);
    exit();
}

// === LẤY ID BÁC SĨ VÀ THÔNG TIN BỆNH NHÂN ===
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tim-bac-si.php");
    exit();
}

$doctor_id = (int) $_GET['id'];
$patient_id = $_SESSION['user_id'];

global $pdo;

// 1. Truy vấn thông tin Bác sĩ
$sql_doctor = "
    SELECT D.full_name AS doctor_name, D.profile_picture, T.department_name
    FROM Doctors D
    JOIN Departments T ON D.department_id = T.department_id
    WHERE D.doctor_id = :id
";
$stmt_doctor = $pdo->prepare($sql_doctor);
$stmt_doctor->execute([':id' => $doctor_id]);
$doctor = $stmt_doctor->fetch();

if (!$doctor) {
    header("Location: tim-bac-si.php");
    exit();
}

// 2. Truy vấn thông tin Bệnh nhân đã đăng nhập
$sql_patient = "
    SELECT full_name, phone_number, date_of_birth, gender
    FROM Patients
    WHERE patient_id = :id
";
$stmt_patient = $pdo->prepare($sql_patient);
$stmt_patient->execute([':id' => $patient_id]);
$patient = $stmt_patient->fetch(PDO::FETCH_ASSOC);

$patient_name = htmlspecialchars($patient['full_name'] ?? '');
$patient_phone = htmlspecialchars($patient['phone_number'] ?? '');
$patient_dob = htmlspecialchars($patient['date_of_birth'] ?? '');
$patient_gender = htmlspecialchars($patient['gender'] ?? '');

$gender_display = '';
if ($patient_gender === 'Male') {
    $gender_display = 'Nam';
} elseif ($patient_gender === 'Female') {
    $gender_display = 'Nữ';
} elseif ($patient_gender === 'Other') {
    $gender_display = 'Khác';
}

// Giả lập các slot thời gian có sẵn
$available_time_slots = [
    '08:00',
    '09:00',
    '10:00',
    '11:00',
    '14:00',
    '15:00',
    '16:00',
    '17:00'
];

$error_message = '';
$success_message = '';

// === XỬ LÝ FORM ĐẶT LỊCH ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_appointment'])) {

    $appointment_date = trim($_POST['ngay_kham'] ?? '');
    $appointment_time = trim($_POST['gio_kham_slot'] ?? '');
    $reason = trim($_POST['ly_do'] ?? '');

    if (empty($appointment_date) || empty($appointment_time) || empty($reason)) {
        $error_message = "Vui lòng điền đầy đủ Ngày Khám, Giờ Khám và Lý do Khám.";
    } else {
        try {
            // Lệnh INSERT ĐÃ SỬA: Bổ sung cột created_at và giá trị NOW()
            $sql_insert = "
                INSERT INTO Appointments 
                (patient_id, doctor_id, appointment_date, appointment_time, reason_for_visit, status, created_at)
                VALUES (:patient_id, :doctor_id, :app_date, :app_time, :reason, 'Pending', NOW())
            ";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                ':patient_id' => $patient_id,
                ':doctor_id' => $doctor_id,
                ':app_date' => $appointment_date,
                ':app_time' => $appointment_time,
                ':reason' => $reason
            ]);

            $success_message = "Đặt lịch thành công!";

        } catch (PDOException $e) {
            // HIỂN THỊ CHI TIẾT LỖI SQL để bạn có thể gửi lại thông báo lỗi cụ thể
            $error_message = "Lỗi SQL: " . $e->getMessage() . " | SQLSTATE: " . $e->getCode() . " | Vui lòng kiểm tra các cột trong bảng Appointments.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lịch Khám với Bác Sĩ <?php echo htmlspecialchars($doctor['doctor_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #198754;
            --background-light: #f8f9fa;
            --background-white: #ffffff;
        }

        body {
            background-color: var(--background-light);
        }

        .booking-container {
            max-width: 950px;
            margin: 40px auto;
            padding: 0;
            border-radius: 25px;
            box-shadow: 15px 15px 30px rgba(180, 180, 180, 0.3),
                -15px -15px 30px rgba(255, 255, 255, 1);
            overflow: hidden;
            background-color: var(--background-white);
        }

        .doctor-info-panel {
            background: linear-gradient(145deg, #007bff, #0056b3);
            color: white;
            padding: 40px 30px;
            text-align: center;
            transform: perspective(1px) translateZ(0);
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .doctor-avatar {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 6px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }

        .form-section {
            padding: 40px;
        }

        .form-group-title {
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--secondary-color);
            border-bottom: 3px solid var(--secondary-color);
            padding-bottom: 8px;
            margin-bottom: 35px;
            text-transform: uppercase;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px;
            box-shadow: inset 2px 2px 5px #efefef, inset -2px -2px 5px #ffffff;
            transition: all 0.2s;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            border-color: var(--primary-color);
        }

        .form-control[readonly],
        .form-control:disabled {
            background-color: #e6e9ee;
            color: #495057;
            border: 1px solid #ced4da;
        }

        .time-slot-btn {
            border: none;
            border-radius: 10px;
            margin: 6px;
            padding: 10px 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: var(--background-light);
            box-shadow: 4px 4px 8px rgba(0, 0, 0, 0.05), -4px -4px 8px rgba(255, 255, 255, 0.7);
            font-weight: 600;
            color: #555;
        }

        .time-slot-btn:hover {
            background-color: #e9ecef;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1), -2px -2px 5px rgba(255, 255, 255, 0.6);
        }

        .time-slot-btn.selected {
            background-color: var(--secondary-color);
            color: white;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.6), inset 0 0 10px rgba(0, 0, 0, 0.3);
            transform: scale(1.05);
        }

        .btn-submit-cta {
            border-radius: 12px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 123, 255, 0.4);
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-submit-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.6);
        }

        .btn-submit-cta:disabled {
            box-shadow: none;
        }

        /* CSS MỚI CHO MODAL */
        .modal-success-icon {
            font-size: 60px;
            color: var(--secondary-color);
            margin-bottom: 20px;
            animation: pulse 1s infinite alternate;
        }

        @keyframes pulse {
            from {
                transform: scale(1);
                opacity: 0.8;
            }

            to {
                transform: scale(1.1);
                opacity: 1;
            }
        }

        .modal-body-custom {
            background: linear-gradient(180deg, #ffffff, #f7fcf9);
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <div class="container">
            <div class="booking-container row g-0">

                <div class="col-md-4 doctor-info-panel">
                    <img src="<?php echo htmlspecialchars($doctor['profile_picture'] ?? './img/no_avatar.png'); ?>"
                        class="rounded-circle doctor-avatar mb-3"
                        alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>">
                    <h3 class="fw-bolder text-white mb-1"><?php echo htmlspecialchars($doctor['doctor_name']); ?></h3>
                    <p class="lead fw-light text-warning"><?php echo htmlspecialchars($doctor['department_name']); ?>
                    </p>
                    <hr class="border-light opacity-50">
                    <p class="small fst-italic mb-0">Hệ thống luôn sẵn sàng hỗ trợ bạn. Mọi thông tin sẽ được giữ bảo
                        mật tuyệt đối.</p>
                </div>

                <div class="col-md-8 form-section">
                    <h2 class="fw-bolder text-dark mb-4">Hoàn Tất Đặt Lịch</h2>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger text-center"><i
                                class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?></div>
                    <?php endif; ?>


                    <form action="dat-lich.php?id=<?php echo $doctor_id; ?>" method="POST" id="appointment-form">

                        <div class="mb-5">
                            <h4 class="form-group-title"><i class="fas fa-user-check me-2"></i> Thông Tin Cá Nhân (Hồ
                                sơ)</h4>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="ho_ten" class="form-label fw-bold">Họ và Tên (*)</label>
                                    <input type="text" class="form-control" value="<?php echo $patient_name; ?>"
                                        readonly disabled>
                                </div>

                                <div class="col-md-6">
                                    <label for="so_dien_thoai" class="form-label fw-bold">Số Điện Thoại (*)</label>
                                    <input type="tel" class="form-control" value="<?php echo $patient_phone; ?>"
                                        readonly disabled>
                                </div>

                                <div class="col-md-6">
                                    <label for="date_of_birth" class="form-label fw-bold">Ngày Sinh</label>
                                    <input type="text" class="form-control"
                                        value="<?php echo empty($patient_dob) ? 'Chưa cập nhật' : date('d/m/Y', strtotime($patient_dob)); ?>"
                                        readonly disabled>
                                </div>

                                <div class="col-md-6">
                                    <label for="gender" class="form-label fw-bold">Giới Tính</label>
                                    <input type="text" class="form-control"
                                        value="<?php echo empty($gender_display) ? 'Chưa cập nhật' : $gender_display; ?>"
                                        readonly disabled>
                                </div>
                                <div class="col-12">
                                    <p class="small text-muted fst-italic">Các thông tin trên được lấy từ hồ sơ. Bạn có
                                        thể cập nhật chúng tại mục Quản lý Hồ sơ.</p>
                                </div>

                            </div>
                        </div>

                        <div class="mb-5">
                            <h4 class="form-group-title"><i class="far fa-clock me-2"></i> Chọn Thời Gian Khám</h4>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="ngay_kham" class="form-label fw-bold">Ngày Khám (*)</label>
                                    <input type="date" class="form-control form-control-lg" id="ngay_kham"
                                        name="ngay_kham" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                        required>
                                    <div class="form-text text-muted">Vui lòng chọn ngày bạn muốn đặt lịch (từ ngày mai
                                        trở đi).</div>
                                </div>

                                <div class="col-md-12 mt-4">
                                    <label class="form-label fw-bold d-block">Giờ Khám (*)</label>

                                    <div id="time-slots-container">
                                        <input type="hidden" name="gio_kham_slot" id="gio_kham_slot" required
                                            data-error="Vui lòng chọn khung giờ khám.">
                                        <?php foreach ($available_time_slots as $slot): ?>
                                            <button type="button" class="time-slot-btn" data-time="<?php echo $slot; ?>:00">
                                                <i class="far fa-clock me-1"></i> <?php echo $slot; ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="form-text mt-2" id="time-slot-error" style="color: red; display: none;">
                                        Vui lòng chọn khung giờ khám.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h4 class="form-group-title"><i class="fas fa-notes-medical me-2"></i> Lý Do Khám</h4>

                            <div class="mb-3">
                                <label for="ly_do" class="form-label fw-bold">Mô tả Triệu chứng/Lý do (*)</label>
                                <textarea class="form-control" id="ly_do" name="ly_do" rows="4"
                                    placeholder="Ví dụ: Đau đầu kéo dài 3 ngày, muốn kiểm tra tổng quát..."
                                    required></textarea>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="submit_appointment"
                                class="btn btn-primary btn-lg fw-bold btn-submit-cta" id="submit-btn">
                                <i class="fas fa-check me-2"></i> XÁC NHẬN VÀ GỬI ĐẶT LỊCH
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header justify-content-center bg-success text-white py-3"
                    style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title fw-bold" id="successModalLabel">
                        THÔNG BÁO THÀNH CÔNG
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center modal-body-custom py-5">
                    <i class="fas fa-check-circle modal-success-icon"></i>
                    <h3 class="text-success fw-bolder mb-3">LỊCH HẸN ĐÃ ĐƯỢC GỬI!</h3>
                    <p class="lead text-dark">Hệ thống đã ghi nhận yêu cầu đặt lịch khám của bạn.</p>
                    <p class="text-muted small">Thông tin chi tiết đang chờ xác nhận từ cơ sở y tế. Vui lòng theo dõi
                        trạng thái lịch hẹn trong tài khoản của bạn.</p>
                </div>
                <div class="modal-footer justify-content-center border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                    <a href="lich-hen.php" class="btn btn-primary fw-bold btn-submit-cta">
                        <i class="far fa-calendar-alt me-2"></i> Xem Lịch Hẹn Của Tôi
                    </a>
                </div>
            </div>
        </div>
    </div>


    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const slotsContainer = document.getElementById('time-slots-container');
            const slotInput = document.getElementById('gio_kham_slot');
            const timeSlots = document.querySelectorAll('.time-slot-btn');
            const form = document.getElementById('appointment-form');
            const submitBtn = document.getElementById('submit-btn');
            const timeSlotError = document.getElementById('time-slot-error');

            // === LOGIC TỰ ĐỘNG HIỂN THỊ MODAL SAU KHI SUBMIT THÀNH CÔNG ===
            <?php if ($success_message && $success_message !== "Đặt lịch thành công!"): ?>
                // Nếu có thông báo lỗi SQL, KHÔNG mở modal thành công.
                // Biến success_message chỉ chứa thông báo "Đặt lịch thành công!"
            <?php elseif ($success_message): ?>
                var successModal = new bootstrap.Modal(document.getElementById('successModal'), {});
                successModal.show();
            <?php endif; ?>
            // =============================================================

            timeSlots.forEach(button => {
                button.addEventListener('click', function () {
                    timeSlots.forEach(btn => btn.classList.remove('selected'));
                    this.classList.add('selected');
                    slotInput.value = this.dataset.time;
                    timeSlotError.style.display = 'none';
                });
            });

            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowFormatted = tomorrow.toISOString().split('T')[0];
            document.getElementById('ngay_kham').min = tomorrowFormatted;


            form.addEventListener('submit', function (e) {

                if (!slotInput.value) {
                    e.preventDefault();
                    timeSlotError.style.display = 'block';
                    timeSlotError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                // Hiệu ứng Loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang xử lý...';
            });

            // Nếu có lỗi (từ PHP), khôi phục nút submit
            <?php if ($error_message): ?>
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check me-2"></i> XÁC NHẬN VÀ GỬI ĐẶT LỊCH';
            <?php endif; ?>
        });
    </script>
</body>

</html>