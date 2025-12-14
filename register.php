<?php require_once 'includes/logic_register.php'; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Tài Khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="css/register.css" rel="stylesheet">
</head>

<body>

    <main>
        <div class="register-card">
            <div class="row g-0">

                <div class="col-md-5 register-image-column">
                    <i class="fas fa-user-plus fa-4x mb-4 opacity-75"></i>
                    <h3 class="fw-bold mb-3">Tham gia ngay!</h3>
                    <p class="mb-0">Đăng ký tài khoản để bắt đầu đặt lịch khám bệnh trực tuyến dễ dàng.</p>
                </div>

                <div class="col-md-7 register-form-column">
                    <h2 class="text-center fw-bolder text-dark mb-2">
                        <span class="text-success">Đăng Ký</span> Tài Khoản Mới
                    </h2>
                    <p class="text-center text-muted mb-4">Hoàn tất các thông tin bên dưới</p>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><i
                                class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><i
                                class="fas fa-check-circle me-2"></i><?php echo $success_message; ?> <a
                                href="login.php">Đăng nhập ngay</a>.</div>
                    <?php endif; ?>

                    <form action="register.php" method="POST">

                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-bold">Họ và Tên:</label>
                            <input type="text" class="form-control form-control-lg" id="full_name" name="full_name"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label fw-bold">Số Điện Thoại (Tài khoản):</label>
                            <input type="tel" class="form-control form-control-lg" id="phone_number" name="phone_number"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email (Không bắt buộc):</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Mật khẩu (ít nhất 6 ký tự):</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-bold">Xác nhận Mật khẩu:</label>
                            <input type="password" class="form-control form-control-lg" id="confirm_password"
                                name="confirm_password" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg fw-bold">
                                <i class="fas fa-user-plus me-2"></i> ĐĂNG KÝ
                            </button>
                        </div>
                    </form>

                    <p class="text-center mt-4">
                        Đã có tài khoản? <a href="login.php" class="text-primary fw-bold">Đăng Nhập</a>
                    </p>

                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>