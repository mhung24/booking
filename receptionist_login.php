<?php
// ... [Phần PHP xử lý logic đăng nhập giữ nguyên như đã sửa trước] ...

// ================= DEBUG =================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =========================================

require_once 'config/connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

global $pdo;
$error_message = '';
$RECEPTIONIST_TABLE = 'Receptionists';
$PASSWORD_COLUMN = 'hashed_pass';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receptionist_login'])) {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Vui lòng nhập đầy đủ Email và Mật khẩu.';
    } else {
        try {

            $sql_check = "SELECT receptionist_id, full_name, {$PASSWORD_COLUMN} FROM {$RECEPTIONIST_TABLE} WHERE email = :email";
            $stmt = $pdo->prepare($sql_check);
            $stmt->execute(['email' => $email]);
            $receptionist = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($receptionist) {
                $hashed_password_from_db = $receptionist[$PASSWORD_COLUMN];

                if (password_verify($password, $hashed_password_from_db)) {

                    $_SESSION['receptionist_id'] = $receptionist['receptionist_id'];
                    $_SESSION['receptionist_name'] = $receptionist['full_name'];
                    $_SESSION['is_receptionist'] = true;

                    header("Location: receptionist_dashboard.php");
                    exit;

                } else {
                    $error_message = 'Email hoặc Mật khẩu không chính xác.';
                }
            } else {
                $error_message = 'Email hoặc Mật khẩu không chính xác.';
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
    <title>Đăng nhập Lễ Tân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #007bff;
            /* Màu xanh chính */
            --light-bg: #f8f9fa;
        }

        body {
            background-color: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }

        .login-box {
            max-width: 800px;
            width: 90%;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            background: #fff;
            display: flex;
        }

        .login-left {
            background-color: var(--primary-blue);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 40%;
            text-align: center;
        }

        .login-left h2 {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-left i {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .login-right {
            padding: 40px;
            width: 60%;
        }

        .form-label-custom {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }

        .btn-login {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            border-radius: 8px;
            padding: 12px 0;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .login-box {
                flex-direction: column;
            }

            .login-left,
            .login-right {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="login-box">
        <div class="login-left">
            <i class="fas fa-shield-alt"></i>
            <h2>Chào mừng trở lại!</h2>
            <p>Đăng nhập để xem lịch hẹn và quản lý thông tin sức khỏe của khách hàng.</p>
        </div>

        <div class="login-right">
            <h2 class="text-center fw-bolder mb-2">Đăng Nhập Hệ Thống</h2>
            <p class="text-center text-muted mb-4">Nhập Email và Mật khẩu</p>

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
                    <button type="submit" name="receptionist_login" class="btn btn-login btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i> ĐĂNG NHẬP
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