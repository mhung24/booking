<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/connect.php';

$page_title = "Trang Chủ - Hệ Thống Đặt Lịch Khám Bệnh";
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

$target_page = "dat-lich.php";

if (!$is_logged_in) {
    $redirect_url = urlencode($target_page);
    $final_link = "login.php?redirect=" . $redirect_url;
} else {
    $final_link = $target_page;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            background-color: #f7f9fc;
        }

        main {
            flex-grow: 1;
        }

        .hero-section {
            background-color: #e6f7ff;
            color: #333;
            padding: 120px 0;
            position: relative;
            overflow: hidden;
        }

        .search-container .form-control {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card {
            border-radius: 12px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .text-primary {
            color: #007bff !important;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            font-weight: 600;
        }

        .btn-outline-primary {
            color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <section class="hero-section text-center">
            <div class="container">
                <h1 class="display-4 fw-bolder mb-3 text-dark">Chăm Sóc Sức Khỏe Dễ Dàng Chỉ Qua Một Cú Chạm</h1>
                <p class="lead mb-5 text-secondary">Tìm kiếm, đặt lịch khám với bác sĩ chuyên khoa hàng đầu mọi lúc, mọi
                    nơi, và quản lý hồ sơ an toàn tuyệt đối.</p>

                <div class="row justify-content-center search-container">
                    <div class="col-lg-8">
                        <div class="input-group input-group-lg shadow-lg rounded-pill p-1 bg-white">
                            <span class="input-group-text bg-white border-0 rounded-start-pill"><i
                                    class="fas fa-search text-secondary"></i></span>
                            <input type="text" class="form-control border-0"
                                placeholder="Tìm kiếm Bác sĩ, Chuyên khoa, hoặc Triệu chứng..." aria-label="Tìm kiếm">
                            <a href="<?php echo $final_link; ?>" class="btn btn-primary rounded-pill px-4 fw-bold">
                                <i class="far fa-calendar-check me-2"></i> ĐẶT LỊCH NHANH
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 bg-white">
            <div class="container">
                <h2 class="text-center mb-5 fw-bold text-primary">Tại Sao Chọn MedBooking?</h2>
                <div class="row text-center">
                    <div class="col-md-4 mb-4">
                        <i class="fas fa-user-md fa-3x text-success mb-3 p-3 rounded-circle bg-light"></i>
                        <h5 class="fw-bold mt-2">Đội Ngũ Chuyên Gia</h5>
                        <p class="text-muted">Bác sĩ hàng đầu, trình độ chuyên môn cao và kinh nghiệm dày dặn.</p>
                    </div>
                    <div class="col-md-4 mb-4">
                        <i class="fas fa-clock fa-3x text-info mb-3 p-3 rounded-circle bg-light"></i>
                        <h5 class="fw-bold mt-2">Quy Trình Nhanh Chóng</h5>
                        <p class="text-muted">Đặt lịch 24/7 chỉ trong 3 bước, tiết kiệm tối đa thời gian chờ đợi.</p>
                    </div>
                    <div class="col-md-4 mb-4">
                        <i class="fas fa-shield-alt fa-3x text-danger mb-3 p-3 rounded-circle bg-light"></i>
                        <h5 class="fw-bold mt-2">Bảo Mật Tuyệt Đối</h5>
                        <p class="text-muted">Hồ sơ điện tử được mã hóa, đảm bảo tính riêng tư cho mọi khách hàng.</p>
                    </div>
                </div>
            </div>
        </section>

        <?php
        include 'includes/department_section.php';
        ?>

        <?php
        include 'includes/doctor_section.php';
        ?>


        <?php include 'includes/customer-reviews.php'; ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>