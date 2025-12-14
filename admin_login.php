<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

global $pdo;
require_once 'includes/logic_admin_login.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/admin_login.css" rel="stylesheet">
</head>

<body>

    <div class="login-box">
        <div class="login-left">
            <i class="fas fa-users-cog"></i>
            <h2>Khu vực Quản Trị</h2>
            <p>Đăng nhập để quản lý tài khoản nhân sự và cài đặt hệ thống.</p>
        </div>

        <div class="login-right">
            <h2 class="text-center fw-bolder mb-2">Đăng Nhập Admin</h2>
            <p class="text-center text-muted mb-4">Chỉ dành cho Quản trị viên và Nhân sự</p>

            <?php if ($error_message): ?>
                <div class="alert alert-danger text-center"><i
                        class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label-custom">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label-custom">Mật khẩu</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" name="admin_login" class="btn btn-login btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i> ĐĂNG NHẬP QUẢN TRỊ
                    </button>
                </div>

                <div class="text-center small">
                    <a href="#" class="text-muted text-decoration-none" style="display:none;">Quên mật khẩu?</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>