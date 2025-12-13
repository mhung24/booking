<?php
require_once 'config/connect.php';
session_start();

$error_message = '';
$success_message = '';

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $phone_number = trim($_POST['phone_number']); // SỬ DỤNG SĐT LÀM ĐỊNH DANH
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Kiểm tra dữ liệu bắt buộc
    if (empty($full_name) || empty($phone_number) || empty($password) || empty($confirm_password)) {
        $error_message = "Vui lòng điền đầy đủ Họ tên, Số điện thoại và Mật khẩu.";
    }
    // 2. Kiểm tra khớp mật khẩu
    else if ($password !== $confirm_password) {
        $error_message = "Xác nhận mật khẩu không khớp.";
    }
    // 3. Kiểm tra độ dài mật khẩu tối thiểu
    else if (strlen($password) < 6) {
        $error_message = "Mật khẩu phải chứa ít nhất 6 ký tự.";
    } else {
        global $pdo;

        // 4. KIỂM TRA SỐ ĐIỆN THOẠI ĐÃ TỒN TẠI CHƯA
        $sql_check = "SELECT patient_id FROM Patients WHERE phone_number = :phone_number";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([':phone_number' => $phone_number]);

        if ($stmt_check->fetch()) {
            $error_message = "Số điện thoại này đã được sử dụng. Vui lòng chọn số khác hoặc Đăng nhập.";
        } else {
            // 5. Mã hóa mật khẩu
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // 6. Chèn dữ liệu vào Database
            $sql_insert = "
                INSERT INTO Patients (full_name, phone_number, password_hash, email) -- Giữ lại email có thể là NULL
                VALUES (:full_name, :phone_number, :password_hash, :email)
            ";

            try {
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    ':full_name' => $full_name,
                    ':phone_number' => $phone_number,
                    ':password_hash' => $password_hash,
                    ':email' => $_POST['email'] ?? null // Đảm bảo email vẫn được gửi nếu có, hoặc là NULL
                ]);

                $success_message = "Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.";

            } catch (PDOException $e) {
                $error_message = "Lỗi hệ thống khi đăng ký: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Tài Khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        body {
            background-color: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-card {
            max-width: 900px;
            width: 90%;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .register-image-column {
            background: linear-gradient(135deg, #198754 0%, #0f5132 100%);
            padding: 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .register-form-column {
            padding: 40px 60px;
            background-color: white;
        }

        .text-success {
            color: #198754 !important;
        }

        .btn-success {
            background-color: #198754;
            border-color: #198754;
            transition: all 0.3s;
        }

        .btn-success:hover {
            background-color: #0f5132;
            border-color: #0f5132;
        }
    </style>
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