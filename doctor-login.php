<?php require_once 'includes/logic_doctor_login.php'; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Bác Sĩ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="css/doctor_login.css" rel="stylesheet">
</head>

<body>

    <main>
        <div class="login-card">
            <div class="row g-0">

                <div class="col-md-5 login-image-column">
                    <i class="fas fa-user-md fa-4x mb-4 opacity-75"></i>
                    <h3 class="fw-bold mb-3">Khu vực Bác Sĩ</h3>
                    <p class="mb-0">Đăng nhập để truy cập lịch làm việc và quản lý các cuộc hẹn.</p>
                </div>

                <div class="col-md-7 login-form-column">
                    <h2 class="text-center fw-bolder text-dark mb-2">
                        Đăng Nhập <span class="text-doctor">Bác Sĩ</span>
                    </h2>
                    <p class="text-center text-muted mb-4">Sử dụng Mã số hành nghề của bạn</p>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><i
                                class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form action="doctor-login.php" method="POST">

                        <div class="mb-3">
                            <label for="license_number" class="form-label fw-bold">Mã số Hành nghề:</label>
                            <input type="text" class="form-control form-control-lg" id="license_number"
                                name="license_number" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">Mật khẩu:</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password"
                                required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-doctor btn-lg fw-bold text-white">
                                <i class="fas fa-sign-in-alt me-2"></i> ĐĂNG NHẬP
                            </button>
                        </div>
                    </form>

                    <p class="text-center mt-4 mb-2 small">
                        <a href="#">Quên mật khẩu?</a> | <a href="login.php">Đăng nhập Bệnh nhân</a>
                    </p>

                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>