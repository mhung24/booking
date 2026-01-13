<?php
// Bật thông báo lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/logic_create_receptionist.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Tài khoản Lễ Tân</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/create_receptionist.css" rel="stylesheet">
</head>

<body>

    <div class="form-card animate-fade-in">

        <div class="header-decor">
            <div class="icon-circle">
                <i class="fas fa-headset fa-3x text-white"></i>
            </div>
        </div>

        <div class="form-content">

            <div class="page-title">
                <h2>Thêm Lễ Tân Mới</h2>
                <p class="text-muted">Mật khẩu mặc định cho tài khoản mới là <b>admin</b></p>
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

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Họ tên Lễ tân <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <i class="far fa-user input-icon"></i>
                            <input type="text" class="form-control" name="full_name" placeholder="VD: Trần Thị B"
                                required>
                        </div>

                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <i class="far fa-envelope input-icon"></i>
                            <input type="email" class="form-control" name="email" placeholder="example@benhvien.com"
                                required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <i class="fas fa-phone-alt input-icon"></i>
                            <input type="tel" class="form-control" name="phone_number" placeholder="09xxxxxxx" required>
                        </div>

                        <label class="form-label">Mật khẩu mặc định</label>
                        <div class="input-group-custom">
                            <i class="fas fa-lock input-icon text-muted"></i>
                            <input type="text" class="form-control bg-light text-muted fw-bold" value="admin" readonly>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="hr_personnel_management.php" class="btn btn-back">
                        <i class="fas fa-arrow-left me-2"></i> Quay lại
                    </a>
                    <button type="submit" name="create_account" class="btn btn-create w-50">
                        <i class="fas fa-user-plus me-2"></i> Tạo Tài Khoản
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
</body>

</html>