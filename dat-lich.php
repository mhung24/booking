<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/logic_booking.php';

// --- PHẦN CODE THUẦN LẤY LỊCH TRỰC TIẾP ---
global $pdo;
$stmt_sch = $pdo->prepare("SELECT DISTINCT work_date FROM doctor_schedules WHERE doctor_id = ? AND work_date >= CURDATE()");
$stmt_sch->execute([$doctor_id]);
$available_dates = $stmt_sch->fetchAll(PDO::FETCH_COLUMN);
// Chuyển mảng PHP sang JSON để Javascript sử dụng trực tiếp
$json_dates = json_encode($available_dates);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đặt lịch khám</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="css/booking.css" rel="stylesheet">
    <style>
        .flatpickr-day.enabled_date {
            background: #d4edda !important;
            border-color: #c3e6cb !important;
            color: #155724 !important;
            font-weight: bold;
        }

        .flatpickr-day.enabled_date:hover {
            background: #c3e6cb !important;
        }

        .flatpickr-day.flatpickr-disabled {
            background: #f8f9fa;
            color: #ccc;
            cursor: not-allowed;
        }

        .flatpickr-day.selected {
            background: #198754 !important;
            border-color: #198754 !important;
            color: #fff !important;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <div class="booking-container row g-0 shadow-lg rounded-4 overflow-hidden bg-white">

            <div class="col-md-4 doctor-info-panel bg-primary text-white p-4 text-center">
                <div class="mb-3">
                    <img src="<?= htmlspecialchars($doctor['profile_picture'] ?? './img/no_avatar.png') ?>"
                        class="rounded-circle border border-4 border-white shadow"
                        style="width: 150px; height: 150px; object-fit: cover;">
                </div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($doctor['doctor_name']) ?></h4>
                <p class="badge bg-warning text-dark mb-3"><?= htmlspecialchars($doctor['department_name']) ?></p>
                <hr class="border-light opacity-50">
                <p class="small fst-italic"><i class="fas fa-shield-alt me-1"></i> Đặt lịch an toàn & bảo mật</p>
            </div>

            <div class="col-md-8 form-section p-4 p-lg-5">
                <h3 class="mb-4 fw-bolder text-dark">Hoàn tất đăng ký khám</h3>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger border-0 shadow-sm mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="appointment-form">
                    <input type="hidden" name="current_doctor_id" value="<?= htmlspecialchars($doctor_id) ?>">

                    <h5 class="fw-bold mb-3"><i class="fas fa-user-circle me-2 text-primary"></i> Thông tin bệnh nhân
                    </h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Họ và tên</label>
                            <input class="form-control bg-light" value="<?= $patient_name ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Số điện thoại</label>
                            <input class="form-control bg-light" value="<?= $patient_phone ?>" disabled>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3"><i class="far fa-calendar-check me-2 text-primary"></i> Lịch khám</h5>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">1. Chọn ngày khám (*)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="fas fa-calendar-day text-primary"></i></span>
                            <input type="text" name="ngay_kham" id="date_picker"
                                class="form-control bg-white border-start-0"
                                placeholder="<?= empty($available_dates) ? 'Bác sĩ chưa có lịch' : 'Chọn ngày khám...' ?>"
                                required readonly <?= empty($available_dates) ? 'disabled' : '' ?>>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">2. Chọn khung giờ (*)</label>
                        <input type="hidden" name="gio_kham_slot" id="gio_kham_slot"
                            value="<?= $_POST['gio_kham_slot'] ?? '' ?>">
                        <div id="time-slot-error" class="alert alert-warning py-2 small mb-3" style="display:none">
                            Vui lòng chọn khung giờ khám.
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($available_time_slots as $slot): ?>
                                <?php $isSelected = (isset($_POST['gio_kham_slot']) && $_POST['gio_kham_slot'] == $slot) ? 'selected' : ''; ?>
                                <button type="button"
                                    class="time-slot-btn btn btn-outline-primary btn-sm <?= $isSelected ?>"
                                    data-time="<?= $slot ?>">
                                    <i class="far fa-clock me-1"></i><?= $slot ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">3. Lý do khám bệnh (*)</label>
                        <textarea name="ly_do" class="form-control" rows="3"
                            required><?= htmlspecialchars($_POST['ly_do'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow py-3"
                        name="submit_appointment" <?= empty($available_dates) ? 'disabled' : '' ?>>
                        XÁC NHẬN ĐẶT LỊCH
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/vn.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Nhận mảng ngày trực tiếp từ PHP
            const availableDates = <?= $json_dates ?>;

            const slotInput = document.getElementById('gio_kham_slot');
            const btns = document.querySelectorAll('.time-slot-btn');
            const err = document.getElementById('time-slot-error');
            const dateInput = document.getElementById('date_picker');

            // Xử lý chọn giờ
            btns.forEach(b => b.addEventListener('click', function () {
                btns.forEach(x => { x.classList.remove('btn-primary', 'text-white'); x.classList.add('btn-outline-primary'); });
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary', 'text-white');
                slotInput.value = this.dataset.time;
                err.style.display = 'none';
            }));

            // Khởi tạo Lịch (Dùng mảng availableDates có sẵn)
            flatpickr("#date_picker", {
                locale: "vn",
                dateFormat: "Y-m-d",
                minDate: "today",
                disable: [
                    function (date) {
                        const d = date.getFullYear() + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" + ("0" + date.getDate()).slice(-2);
                        return !availableDates.includes(d);
                    }
                ],
                onDayCreate: function (dObj, dStr, fp, dayElem) {
                    const d = dayElem.dateObj.getFullYear() + "-" + ("0" + (dayElem.dateObj.getMonth() + 1)).slice(-2) + "-" + ("0" + dayElem.dateObj.getDate()).slice(-2);
                    if (availableDates.includes(d)) { dayElem.classList.add("enabled_date"); }
                }
            });

            document.getElementById('appointment-form').onsubmit = e => {
                if (!slotInput.value) { e.preventDefault(); err.style.display = 'block'; }
            };

            <?php if ($success_message === 'success'): ?>
                new bootstrap.Modal(document.getElementById('successModal')).show();
            <?php endif; ?>
        });
    </script>

    <div class="modal fade" id="successModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg text-center p-4">
                <div class="mb-3"><i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i></div>
                <h3 class="fw-bold text-success">Đặt lịch thành công!</h3>
                <a href="lich-hen.php" class="btn btn-success btn-lg rounded-pill fw-bold">Xem lịch của tôi</a>
            </div>
        </div>
    </div>
</body>

</html>