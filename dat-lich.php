<?php
// ================= DEBUG =================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =========================================

require_once 'config/connect.php';
if (session_status() === PHP_SESSION_NONE)
    session_start();

$error_message = '';
$success_message = '';

$patient_id = $_SESSION['user_id'] ?? null;
$doctor_id = (int) ($_GET['id'] ?? 0);

if (!$patient_id) {
    header('Location: login.php');
    exit;
}

$available_time_slots = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];

global $pdo;

// ===== DOCTOR =====
$stmt = $pdo->prepare("SELECT D.full_name AS doctor_name, D.profile_picture, T.department_name
                         FROM Doctors D JOIN Departments T ON D.department_id = T.department_id
                         WHERE D.doctor_id = :id");
$stmt->execute(['id' => $doctor_id]);
$doctor = $stmt->fetch();
if (!$doctor) {
    header('Location: tim-bac-si.php');
    exit;
}

// ===== PATIENT =====
$stmt = $pdo->prepare("SELECT full_name, phone_number, date_of_birth, gender FROM Patients WHERE patient_id = :id");
$stmt->execute(['id' => $patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

$patient_name = htmlspecialchars($patient['full_name'] ?? '');
$patient_phone = htmlspecialchars($patient['phone_number'] ?? '');
$patient_dob = htmlspecialchars($patient['date_of_birth'] ?? 'Chưa cập nhật');

$gender_display = match ($patient['gender'] ?? '') {
    'Male' => 'Nam',
    'Female' => 'Nữ',
    'Other' => 'Khác',
    default => 'Chưa cập nhật'
};

// ===== SUBMIT =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_appointment'])) {

    $appointment_date = $_POST['ngay_kham'] ?? '';
    $appointment_time = $_POST['gio_kham_slot'] ?? '';
    $reason = trim($_POST['ly_do'] ?? '');

    if (!$appointment_date || !$appointment_time || !$reason) {
        $error_message = 'Vui lòng điền đầy đủ thông tin.';
    }

    if (preg_match('/^\d{2}:\d{2}$/', $appointment_time)) {
        $appointment_time .= ':00';
    }

    if (!DateTime::createFromFormat('Y-m-d', $appointment_date)) {
        $error_message = 'Ngày khám không hợp lệ.';
    }

    if (!$error_message) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Appointments
                (patient_id, doctor_id, appointment_date, appointment_time, reason_for_visit, status, created_at)
                VALUES (:p, :d, :ad, :at, :r, 'Pending', NOW())");

            $stmt->execute([
                'p' => $patient_id,
                'd' => $doctor_id,
                'ad' => $appointment_date,
                'at' => $appointment_time,
                'r' => $reason
            ]);

            $success_message = 'success';

        } catch (PDOException $e) {
            $error_message = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đặt lịch khám</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #198754;
            --bg-light: #f4f6f9;
            --text-dark: #343a40;
        }

        body {
            background: var(--bg-light);
            color: var(--text-dark);
        }

        .booking-container {
            max-width: 950px;
            margin: 40px auto;
            border-radius: 15px;
            /* Giảm độ cong */
            overflow: hidden;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            /* Shadow hiện đại */
        }

        .doctor-info-panel {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: #fff;
            padding: 40px 30px;
            text-align: center;
        }

        .doctor-avatar {
            width: 100px;
            /* Nhỏ hơn một chút */
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .form-section {
            padding: 40px;
            background: #fff
        }

        .form-group-title {
            font-size: 1.4rem;
            /* Nhỏ hơn một chút */
            font-weight: 700;
            color: var(--secondary-color);
            border-bottom: 2px solid var(--secondary-color);
            /* Mỏng hơn */
            padding-bottom: 5px;
            margin-bottom: 25px;
        }

        .time-slot-btn {
            margin: 4px;
            padding: 10px 18px;
            border-radius: 8px;
            border: 1px solid #ccc;
            background-color: #f0f0f0;
            font-weight: 500;
            transition: all 0.2s;
        }

        .time-slot-btn:hover {
            background-color: #e9ecef;
        }

        .time-slot-btn.selected {
            background: var(--secondary-color);
            color: #fff;
            border-color: var(--secondary-color);
            box-shadow: 0 4px 8px rgba(25, 135, 84, 0.3);
        }

        .form-control:disabled {
            background-color: #e9ecef;
        }

        .modal-success-icon {
            font-size: 50px;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }

        .btn-submit {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.2s;
        }

        .btn-submit:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="booking-container row g-0">

            <div class="col-md-4 doctor-info-panel">
                <img src="<?= htmlspecialchars($doctor['profile_picture'] ?? './img/no_avatar.png') ?>"
                    class="doctor-avatar mb-3">
                <h4 class="mb-1"><?= htmlspecialchars($doctor['doctor_name']) ?></h4>
                <p class="small text-warning"><?= htmlspecialchars($doctor['department_name']) ?></p>
                <hr class="border-light opacity-50">
                <p class="small fst-italic">Đặt lịch dễ dàng, bảo mật tuyệt đối.</p>
            </div>

            <div class="col-md-8 form-section">
                <h3 class="mb-4 fw-bolder text-dark">Hoàn tất đặt lịch</h3>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger text-center"><i
                            class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?></div>
                <?php endif; ?>

                <form method="POST" id="appointment-form">

                    <h4 class="form-group-title"><i class="fas fa-user-check me-2"></i> Thông tin cá nhân</h4>
                    <div class="row g-3">
                        <div class="col-md-6"><input class="form-control" value="<?= $patient_name ?>" disabled></div>
                        <div class="col-md-6"><input class="form-control" value="<?= $patient_phone ?>" disabled></div>
                        <div class="col-md-6"><input class="form-control" value="<?= $patient_dob ?>" disabled></div>
                        <div class="col-md-6"><input class="form-control" value="<?= $gender_display ?>" disabled></div>
                    </div>
                    <p class="small text-muted fst-italic mt-2">Dữ liệu từ hồ sơ. Vui lòng cập nhật nếu cần.</p>


                    <h4 class="form-group-title mt-5"><i class="far fa-clock me-2"></i> Chọn thời gian</h4>
                    <label class="form-label fw-bold small">Ngày khám (*)</label>
                    <input type="date" name="ngay_kham" class="form-control mb-3"
                        min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>

                    <label class="form-label fw-bold small">Giờ khám (*)</label>
                    <input type="hidden" name="gio_kham_slot" id="gio_kham_slot">
                    <div id="time-slot-error" class="text-danger mb-3 small" style="display:none">Vui lòng chọn khung
                        giờ khám.</div>

                    <div class="d-flex flex-wrap mb-4">
                        <?php foreach ($available_time_slots as $slot): ?>
                            <button type="button" class="time-slot-btn" data-time="<?= $slot ?>"><i
                                    class="far fa-clock me-1"></i>
                                <?= $slot ?></button>
                        <?php endforeach; ?>
                    </div>


                    <h4 class="form-group-title mt-5"><i class="fas fa-notes-medical me-2"></i> Lý do khám</h4>
                    <label class="form-label fw-bold small">Mô tả Triệu chứng/Lý do (*)</label>
                    <textarea name="ly_do" class="form-control" rows="3" required></textarea>

                    <button class="btn btn-primary btn-lg w-100 mt-4 fw-bold btn-submit" name="submit_appointment"
                        id="submit-btn">
                        <i class="fas fa-check me-2"></i> XÁC NHẬN VÀ GỬI ĐẶT LỊCH
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const slot = document.getElementById('gio_kham_slot');
            const btns = document.querySelectorAll('.time-slot-btn');
            const err = document.getElementById('time-slot-error');
            btns.forEach(b => b.onclick = () => { btns.forEach(x => x.classList.remove('selected')); b.classList.add('selected'); slot.value = b.dataset.time; err.style.display = 'none'; });
            document.getElementById('appointment-form').onsubmit = e => { if (!slot.value) { e.preventDefault(); err.style.display = 'block'; } };
<?php if ($success_message): ?>new bootstrap.Modal(document.getElementById('successModal')).show(); <?php endif; ?>
        });
    </script>


    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <i class="fas fa-check-circle modal-success-icon"></i>
                <h4 class="text-success fw-bold">Đặt lịch thành công</h4>
                <p>Yêu cầu của bạn đã được gửi.</p>
                <a href="lich-hen.php" class="btn btn-success mt-3">Xem lịch hẹn</a>
            </div>
        </div>
    </div>

</body>

</html>