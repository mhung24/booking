<?php require_once 'includes/logic_hr_personnel_management.php'; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>HR Admin - Quản lý Nhân sự</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/hr_personnel_management.css" rel="stylesheet">
</head>

<body>

    <div class="dashboard-header animate-fade-in">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white bg-opacity-10 p-2 rounded-circle border border-white border-opacity-25">
                    <i class="fas fa-users-cog fa-2x text-white"></i>
                </div>
                <div>
                    <h2 class="header-title mb-0">HR Dashboard</h2>
                    <div class="user-badge mt-1">
                        <i class="fas fa-user-shield me-1"></i>
                        Xin chào, <strong><?= htmlspecialchars($admin_name) ?></strong>
                    </div>
                </div>
            </div>
            <a href="logout.php" class="btn btn-logout px-4 py-2 rounded-pill fw-bold">
                <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
            </a>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row justify-content-center mb-5 animate-fade-in delay-1">
            <div class="col-md-8 text-center">
                <h3 class="text-white fw-bold mb-3">Trung tâm Quản trị Nhân sự</h3>
                <p class="text-light opacity-75 fs-5">Lựa chọn chức năng quản lý bên dưới để bắt đầu</p>
            </div>
        </div>

        <div class="row g-4 justify-content-center">

            <div class="col-lg-4 col-md-6 animate-fade-in delay-1">
                <a href="create_doctor.php" class="text-decoration-none">
                    <div class="function-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h4 class="card-title">Tạo Bác sĩ Mới</h4>
                        <p class="card-text">Thêm hồ sơ bác sĩ, chuyên khoa và thông tin đăng nhập vào hệ thống.</p>
                        <div class="mt-3 text-primary fw-bold small text-uppercase tracking-wider">
                            Truy cập <i class="fas fa-arrow-right ms-1"></i>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6 animate-fade-in delay-2">
                <a href="create_receptionist.php" class="text-decoration-none">
                    <div class="function-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4 class="card-title">Tạo Lễ tân Mới</h4>
                        <p class="card-text">Cấp quyền truy cập cho nhân viên tiếp đón và quản lý lịch hẹn.</p>
                        <div class="mt-3 text-primary fw-bold small text-uppercase tracking-wider">
                            Truy cập <i class="fas fa-arrow-right ms-1"></i>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6 animate-fade-in delay-3">
                <a href="manage_personnel.php" class="text-decoration-none">
                    <div class="function-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-users-gear"></i>
                        </div>
                        <h4 class="card-title">Danh sách Nhân sự</h4>
                        <p class="card-text">Xem toàn bộ nhân viên, chỉnh sửa thông tin hoặc vô hiệu hóa tài khoản.</p>
                        <div class="mt-3 text-primary fw-bold small text-uppercase tracking-wider">
                            Truy cập <i class="fas fa-arrow-right ms-1"></i>
                        </div>
                    </div>
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>