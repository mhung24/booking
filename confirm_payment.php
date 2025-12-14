<?php require_once 'includes/logic_confirm_payment.php'; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Xác Nhận Lịch Hẹn & Thanh Toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/confirm_payment.css" rel="stylesheet">
</head>

<body>

    <div class="container">
        <div class="card confirmation-card">
            <div class="card-header-custom">
                <h3 class="mb-0"><i class="fas fa-check-circle me-2"></i> Xác Nhận Lịch Hẹn</h3>
                <p class="mb-0 opacity-75">Kiểm tra thông tin trước khi chuyển cho bác sĩ</p>
            </div>

            <div class="card-body p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <h5 class="text-primary fw-bold mb-3 border-bottom pb-2">1. Thông Tin Bệnh Nhân</h5>
                <div class="info-row">
                    <span class="info-label">Họ tên:</span>
                    <span class="info-value"><?= htmlspecialchars($data['patient_name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Số điện thoại:</span>
                    <span class="info-value"><?= htmlspecialchars($data['phone_number']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Mã BHYT:</span>
                    <span
                        class="info-value text-success"><?= !empty($data['bhyt_code']) ? $data['bhyt_code'] : 'Không có' ?></span>
                </div>

                <h5 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-4">2. Thông Tin Khám</h5>
                <div class="info-row">
                    <span class="info-label">Bác sĩ phụ trách:</span>
                    <span class="info-value"><?= htmlspecialchars($data['doctor_name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Chuyên khoa:</span>
                    <span class="info-value"><?= htmlspecialchars($data['department_name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Thời gian:</span>
                    <span class="info-value">
                        <?= date('H:i', strtotime($data['appointment_time'])) ?> -
                        <?= date('d/m/Y', strtotime($data['appointment_date'])) ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Lý do khám:</span>
                    <span
                        class="info-value text-muted fst-italic"><?= htmlspecialchars($data['reason_for_visit']) ?></span>
                </div>

                <?php if (!empty($data['service_name'])): ?>
                    <div class="total-section text-center">
                        <p class="mb-1 text-muted">Dịch vụ: <strong><?= $data['service_name'] ?></strong></p>
                        <div class="d-flex justify-content-between align-items-center px-5">
                            <span>Phí dịch vụ:</span>
                            <span
                                class="total-price"><?= number_format($data['paid_amount'] > 0 ? $data['paid_amount'] : $data['service_price'], 0, ',', '.') ?>
                                VNĐ</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mt-3 small">
                        <i class="fas fa-exclamation-circle"></i> Chưa chọn dịch vụ cụ thể. Vui lòng cập nhật hồ sơ nếu cần
                        thu phí trước.
                    </div>
                <?php endif; ?>

                <form method="POST" class="mt-4 d-grid gap-2 d-md-flex justify-content-md-center">
                    <input type="hidden" name="confirm_action" value="1">

                    <a href="receptionist_dashboard.php" class="btn btn-outline-secondary btn-lg px-4">
                        <i class="fas fa-arrow-left me-2"></i> Quay lại
                    </a>

                    <button type="submit" class="btn btn-success btn-lg px-5 fw-bold shadow">
                        <i class="fas fa-check me-2"></i> XÁC NHẬN & IN PHIẾU
                    </button>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>