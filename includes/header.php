<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'Tài Khoản';
?>

<style>
    /* CSS cho nút Đăng Nhập trong Header */
    .header-login-btn {
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        color: #007bff;
        border-color: #007bff;
    }

    .header-login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        background-color: #007bff;
        color: white;
    }

    /* CSS cho nút Đặt Lịch trong Header */
    .header-cta-btn {
        background-color: #198754 !important;
        border-color: #198754 !important;
        font-weight: bold;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    .header-cta-btn:hover {
        background-color: #157347 !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
    }

    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        color: #007bff !important;
    }

    /* Buộc dropdown-menu hiển thị khi hover vào nút cha */
    .dropdown:hover>.dropdown-menu {
        display: block;
        margin-top: 0;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">

        <a class="navbar-brand" href="index.php">
            <i class="fas fa-heartbeat me-2 text-danger"></i> HealthBooking
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link" href="tim-bac-si.php">Tìm Bác Sĩ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="chuyen-khoa.php">Chuyên Khoa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dich-vu.php">Dịch Vụ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="lich-hen.php">Lịch Hẹn</a>
                </li>
            </ul>

            <div class="d-flex align-items-center">

                <?php if ($is_logged_in): ?>
                    <div class="dropdown d-none d-lg-inline-block me-3">
                        <button class="btn btn-outline-primary dropdown-toggle header-login-btn" type="button"
                            aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> Xin chào, <?php echo htmlspecialchars($user_name); ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="lich-hen.php"><i class="far fa-calendar-alt me-2"></i> Lịch
                                    Hẹn Của Tôi</a></li>
                            <li><a class="dropdown-item" href="thong-tin-ca-nhan.php"><i class="fas fa-cog me-2"></i> Quản
                                    lý Hồ sơ</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i
                                        class="fas fa-sign-out-alt me-2"></i> Đăng Xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary me-3 header-login-btn d-none d-lg-inline-block">
                        <i class="fas fa-user-circle me-1"></i> Đăng Nhập
                    </a>
                <?php endif; ?>

                <a href="dat-lich.php" class="btn btn-success header-cta-btn d-none d-lg-inline-block">
                    <i class="far fa-calendar-check me-1"></i> ĐẶT LỊCH NGAY
                </a>
            </div>
        </div>
    </div>
</nav>