<?php
// ================= DEBUG =================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =========================================

require_once 'config/connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- ⚠️ BẢO VỆ TRANG CHỈ DÀNH CHO ADMIN VÀ HR_ADMIN ---
$allowed_roles = ['Super', 'HR_Admin'];

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    // Chuyển hướng người không có quyền về trang đăng nhập hoặc trang lỗi
    header('Location: login.php');
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'Quản lý';
$admin_role = $_SESSION['user_role'] ?? 'Admin';

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Nhân sự (HR Admin)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --hr-color: #ffc107;
            /* Màu vàng cam cho HR */
        }

        body {
            background-color: #f4f6f9;
        }

        .dashboard-header {
            background-color: var(--hr-color);
            color: #343a40;
            padding: 25px 0;
            margin-bottom: 30px;
            border-bottom: 5px solid #e0a800;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .icon-box {
            font-size: 3rem;
            color: var(--hr-color);
            margin-bottom: 15px;
        }

        .logout-btn {
            background: none;
            border: none;
            color: white;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .logout-btn:hover {
            opacity: 1;
        }
    </style>
</head>

<body>

    <div class="dashboard-header">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0 fw-bold"><i class="fas fa-users-cog me-2"></i> Dashboard Quản lý Nhân sự</h1>
                <p class="mb-0">Xin chào, **<?= htmlspecialchars($admin_name) ?>** (Vai trò:
                    <?= htmlspecialchars($admin_role) ?>)</p>
            </div>
            <a href="logout.php" class="btn btn-dark"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
        </div>
    </div>

    <div class="container">
        <h3 class="mb-4 text-center text-secondary">Chọn chức năng quản lý:</h3>

        <div class="row g-4">

            <div class="col-md-4">
                <a href="create_doctor.php" class="text-decoration-none">
                    <div class="card text-center h-100 p-4">
                        <div class="card-body">
                            <div class="icon-box"><i class="fas fa-user-md"></i></div>
                            <h5 class="card-title fw-bold">Tạo Tài khoản Bác sĩ</h5>
                            <p class="card-text text-muted">Thêm nhân viên y tế mới vào hệ thống khám bệnh.</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="create_receptionist.php" class="text-decoration-none">
                    <div class="card text-center h-100 p-4">
                        <div class="card-body">
                            <div class="icon-box"><i class="fas fa-user-tie"></i></div>
                            <h5 class="card-title fw-bold">Tạo Tài khoản Lễ Tân</h5>
                            <p class="card-text text-muted">Thêm nhân viên quản lý lịch hẹn và hồ sơ khách hàng.</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="manage_personnel.php" class="text-decoration-none">
                    <div class="card text-center h-100 p-4">
                        <div class="card-body">
                            <div class="icon-box"><i class="fas fa-clipboard-list"></i></div>
                            <h5 class="card-title fw-bold">Quản lý Tài khoản</h5>
                            <p class="card-text text-muted">Xem, sửa, hoặc vô hiệu hóa tài khoản nhân viên hiện có.</p>
                        </div>
                    </div>
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>