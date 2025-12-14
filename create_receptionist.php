<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/logic_create_receptionist.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Tạo Tài khoản Lễ Tân (Admin Only)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/create_receptionist.css" rel="stylesheet">
</head>

<body>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4"><i class="fas fa-user-plus me-2"></i> Tạo Tài khoản Lễ Tân</h2>

            <?php if ($message): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label for="full_name" class="form-label">Họ tên Lễ tân (*)</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email (*)</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="phone_number" class="form-label">Số điện thoại (*)</label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Mật khẩu (*)</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="form-text">Mật khẩu tối thiểu 6 ký tự.</div>
                </div>

                <div class="d-grid">
                    <button type="submit" name="create_account" class="btn btn-create btn-lg">
                        <i class="fas fa-user-plus me-1"></i> TẠO TÀI KHOẢN
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>