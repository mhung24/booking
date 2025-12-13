<?php
require_once 'config/connect.php';
session_start();

$error_message = '';
$success_message = '';

// Kiểm tra nếu Bác sĩ đã đăng nhập, chuyển hướng về trang quản lý của họ
if (isset($_SESSION['doctor_id']) && !empty($_SESSION['doctor_id'])) {
    header("Location: doctor-dashboard.php");
    exit();
}

// Xử lý Form Đăng Nhập Bác Sĩ
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $license_number = trim($_POST['license_number']); // Sử dụng Mã số hành nghề làm Tên đăng nhập
    $password = $_POST['password'];

    if (empty($license_number) || empty($password)) {
        $error_message = "Vui lòng nhập Mã số hành nghề và Mật khẩu.";
    } else {
        global $pdo;

        // 1. Tìm kiếm Bác sĩ theo Mã số hành nghề
        $sql = "SELECT doctor_id, full_name, password_hash FROM Doctors WHERE license_number = :license_number";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':license_number' => $license_number]);
        $doctor = $stmt->fetch();

        if ($doctor) {
            // 2. Kiểm tra Mật khẩu (Giả định mật khẩu đã được hash và lưu trong Doctors.password_hash)
            if (password_verify($password, $doctor['password_hash'])) {

                // Đăng nhập thành công
                $_SESSION['doctor_id'] = $doctor['doctor_id'];
                $_SESSION['doctor_name'] = $doctor['full_name'];

                // Chuyển hướng đến trang quản lý lịch hẹn của Bác sĩ
                header("Location: doctor-dashboard.php");
                exit();

            } else {
                $error_message = "Mật khẩu không chính xác.";
            }
        } else {
            $error_message = "Mã số hành nghề không đúng hoặc chưa được đăng ký.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Bác Sĩ</title>
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

        .login-card {
            max-width: 900px;
            width: 90%;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .login-image-column {
            /* Sử dụng màu tím hoặc đỏ để phân biệt với Bệnh nhân */
            background: linear-gradient(135deg, #6f42c1 0%, #4c2980 100%);
            padding: 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .login-form-column {
            padding: 40px 60px;
            background-color: white;
        }

        .text-doctor {
            /* Màu tím cho Bác sĩ */
            color: #6f42c1 !important;
        }

        .btn-doctor {
            background-color: #6f42c1;
            border-color: #6f42c1;
            transition: all 0.3s;
        }

        .btn-doctor:hover {
            background-color: #4c2980;
            border-color: #4c2980;
        }
    </style>
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