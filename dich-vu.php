<?php require_once 'includes/logic_dich_vu.php'; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lịch Khám với Bác Sĩ <?php echo htmlspecialchars($doctor['doctor_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="css/dich_vu.css" rel="stylesheet">
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

            <?php if ($success_message && $success_message !== "Đặt lịch thành công!"): ?>
            <?php elseif ($success_message): ?>
                var successModal = new bootstrap.Modal(document.getElementById('successModal'), {});
                successModal.show();
            <?php endif; ?>

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

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang xử lý...';
            });

            <?php if ($error_message): ?>
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check me-2"></i> XÁC NHẬN VÀ GỬI ĐẶT LỊCH';
            <?php endif; ?>
        });
    </script>
</body>

</html>