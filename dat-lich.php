<?php
// ================= DEBUG =================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =========================================

require_once 'includes/logic_booking.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đặt lịch khám</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/booking.css" rel="stylesheet">
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