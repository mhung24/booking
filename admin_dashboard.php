<?php
require_once 'includes/logic_admin_dashboard.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hệ thống Bệnh viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/admin_dashboard.css" rel="stylesheet">
</head>

<body>

    <nav class="glass-nav sticky-top">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                    style="width:40px; height:40px">
                    <i class="fas fa-hospital-alt"></i>
                </div>
                <h5 class="m-0 fw-bold text-dark">Hospital Admin</h5>
            </div>

            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle rounded-pill px-3 fw-bold text-secondary" type="button"
                    data-bs-toggle="dropdown">
                    <i class="fas fa-user-shield me-2"></i>
                    <?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow mt-2">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="login.php"><i
                                class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">

        <div class="mb-4 text-white">
            <h2 class="fw-bold">Xin chào, <?= htmlspecialchars($_SESSION['full_name'] ?? 'Quản trị viên') ?>! 👋</h2>
            <p class="opacity-75">Dưới đây là tổng quan tình hình nhân sự hệ thống hôm nay.</p>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="stats-card card-blue">
                    <div class="stats-icon"><i class="fas fa-user-md"></i></div>
                    <div class="stats-number"><?= $stats['count_doctors'] ?></div>
                    <div class="stats-label">Bác sĩ</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card card-pink">
                    <div class="stats-icon"><i class="fas fa-headset"></i></div>
                    <div class="stats-number"><?= $stats['count_receptionists'] ?></div>
                    <div class="stats-label">Lễ tân</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card card-orange">
                    <div class="stats-icon"><i class="fas fa-user-shield"></i></div>
                    <div class="stats-number"><?= $stats['count_admins'] ?></div>
                    <div class="stats-label">Quản trị viên</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card card-green">
                    <div class="stats-icon"><i class="fas fa-clinic-medical"></i></div>
                    <div class="stats-number"><?= $stats['count_departments'] ?></div>
                    <div class="stats-label">Chuyên khoa</div>
                </div>
            </div>
        </div>

        <div class="row g-4">

            <div class="col-lg-8">
                <div class="dashboard-panel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="panel-title m-0"><i class="fas fa-history text-primary"></i> Nhân sự mới gia nhập
                        </div>
                        <a href="manage_personnel.php" class="btn btn-sm btn-outline-primary rounded-pill">Xem tất
                            cả</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="text-secondary small">
                                <tr>
                                    <th>Họ và Tên</th>
                                    <th>Vai trò</th>
                                    <th>Ngày tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_list)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Chưa có dữ liệu mới.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_list as $user): ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($user['full_name']) ?></td>
                                            <td>
                                                <?php if ($user['role'] == 'Doctor'): ?>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">Bác
                                                        sĩ</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Lễ
                                                        tân</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="dashboard-panel">
                    <div class="panel-title"><i class="fas fa-rocket text-warning"></i> Phím tắt quản lý</div>

                    <a href="manage_personnel.php" class="btn-quick">
                        <i class="fas fa-users text-primary"></i>
                        <span>Quản lý Nhân sự</span>
                        <i class="fas fa-arrow-right ms-auto text-muted small"></i>
                    </a>

                    <a href="create_doctor.php" class="btn-quick">
                        <i class="fas fa-user-plus text-success"></i>
                        <span>Thêm Bác sĩ mới</span>
                        <i class="fas fa-arrow-right ms-auto text-muted small"></i>
                    </a>

                    <a href="create_receptionist.php" class="btn-quick">
                        <i class="fas fa-headset text-danger"></i>
                        <span>Thêm Lễ tân mới</span>
                        <i class="fas fa-arrow-right ms-auto text-muted small"></i>
                    </a>


                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>