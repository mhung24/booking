<?php
// ================= DEBUG =================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =========================================

require_once 'config/connect.php';
global $pdo;

$message = '';
$error_message = '';

// Tên bảng và cột mật khẩu của Lễ tân (Kiểm tra lại tên cột trong DB của bạn)
$RECEPTIONIST_TABLE = 'Receptionists';
$PASSWORD_COLUMN = 'hashed_pass';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_account'])) {

    // 1. Lấy dữ liệu
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone_number = trim($_POST['phone_number'] ?? '');

    // 2. Kiểm tra dữ liệu
    if (empty($full_name) || empty($email) || empty($password) || empty($phone_number)) {
        $error_message = 'Vui lòng nhập đầy đủ Họ tên, Email, SĐT và Mật khẩu.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } else {
        try {
            // Kiểm tra trùng Email
            $sql_check = "SELECT COUNT(*) FROM {$RECEPTIONIST_TABLE} WHERE email = :email";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute(['email' => $email]);
            if ($stmt_check->fetchColumn() > 0) {
                $error_message = 'Email này đã được sử dụng cho một tài khoản Lễ tân khác.';
            } else {

                // 3. MÃ HÓA MẬT KHẨU
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // 4. CHÈN DỮ LIỆU VÀO DATABASE
                $sql_insert = "
                    INSERT INTO {$RECEPTIONIST_TABLE} (full_name, email, phone_number, {$PASSWORD_COLUMN}, created_at) 
                    VALUES (:full_name, :email, :phone_number, :hashed_pass, NOW())
                ";

                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    'full_name' => $full_name,
                    'email' => $email,
                    'phone_number' => $phone_number,
                    'hashed_pass' => $hashed_password
                ]);

                $message = "Đã tạo tài khoản Lễ tân **{$full_name}** thành công!";
            }

        } catch (PDOException $e) {
            $error_message = 'Lỗi hệ thống database: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Tạo Tài khoản Lễ Tân (Admin Only)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .form-container {
            max-width: 550px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-create {
            background-color: #28a745;
            border-color: #28a745;
        }
    </style>
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