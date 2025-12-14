<?php
require_once 'includes/logic_create_doctor.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Tài Khoản Bác Sĩ</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/create_doctor.css" rel="stylesheet">
</head>

<body>

    <div class="form-card animate-fade-in">

        <div class="header-decor">
            <div class="icon-circle">
                <i class="fas fa-user-md fa-3x text-white"></i>
            </div>
        </div>

        <div class="form-content">

            <div class="page-title">
                <h2>Thêm Bác Sĩ Mới</h2>
                <p class="text-muted">Điền thông tin để cấp quyền truy cập hệ thống</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4"
                    style="background: #dcfce7; color: #166534;">
                    <i class="fas fa-check-circle fs-4 me-3"></i>
                    <div><?= $message ?></div>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4"
                    style="background: #fee2e2; color: #991b1b;">
                    <i class="fas fa-exclamation-triangle fs-4 me-3"></i>
                    <div><strong>Lỗi!</strong> <?= $error_message ?></div>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-section-title">
                    <i class="far fa-id-card text-primary"></i> Thông tin cá nhân
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Họ và Tên <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <i class="far fa-user input-icon"></i>
                            <input type="text" class="form-control" name="full_name" placeholder="VD: Nguyễn Văn A"
                                required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <i class="fas fa-phone-alt input-icon"></i>
                            <input type="tel" class="form-control" name="phone_number"
                                placeholder="09xxxx (Mật khẩu mặc định)" required>
                        </div>
                    </div>
                </div>

                <div class="form-section-title mt-2">
                    <i class="fas fa-briefcase-medical text-primary"></i> Chuyên môn & Tài khoản
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Mã hành nghề (Tự động)</label>
                        <div class="input-group-custom">
                            <i class="fas fa-id-badge input-icon text-primary"></i>
                            <input type="text" class="form-control bg-light text-primary fw-bold" name="license_code"
                                value="" readonly disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Chuyên khoa <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <i class="fas fa-hospital-user input-icon"></i>
                            <select class="form-select" name="department_id" required>
                                <option value="" selected disabled>Chọn chuyên khoa...</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                        <?= htmlspecialchars($dept['department_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Trình độ / Bằng cấp <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <i class="fas fa-graduation-cap input-icon"></i>
                            <textarea class="form-control" name="qualification" rows="2"
                                placeholder="VD: Tiến sĩ Y khoa, Bác sĩ CKI..." required
                                style="padding-left: 45px;"></textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="hr_personnel_management.php" class="btn btn-back text-decoration-none">
                        <i class="fas fa-long-arrow-alt-left me-2"></i> Quay lại
                    </a>
                    <button type="submit" name="create_doctor" class="btn btn-submit">
                        <i class="fas fa-user-plus me-2"></i> Tạo Tài Khoản
                    </button>
                </div>

            </form>
        </div>
    </div>

    <style>
        .animate-fade-in {
            animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>


</html>