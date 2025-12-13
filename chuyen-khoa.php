<?php
require_once 'config/connect.php';

function render_stars(float $rating): string
{
    $html = '';
    $full_stars = floor($rating);
    $has_half = ($rating - $full_stars) >= 0.5;
    $empty_stars = 5 - ceil($rating);

    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    if ($has_half) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    return $html;
}

$selected_department = $_GET['dept'] ?? null;
$doctors_by_department = [];
$page_title = "Tất Cả Chuyên Khoa";
$default_avatar = './img/no_avatar.png';

global $pdo;

$sql_all_departments = "SELECT department_name, icon_class, description FROM Departments ORDER BY department_name ASC";
$stmt_all_departments = $pdo->prepare($sql_all_departments);
$stmt_all_departments->execute();
$all_departments = $stmt_all_departments->fetchAll();

if ($selected_department) {
    $page_title = "Bác Sĩ Chuyên Khoa " . $selected_department;
    $sql_doctors = "
        SELECT 
            D.doctor_id,
            D.full_name, 
            D.profile_picture, 
            D.biography,
            T.department_name
        FROM Doctors D
        JOIN Departments T ON D.department_id = T.department_id
        WHERE T.department_name = :dept_name AND D.status = 'ACTIVE'
        ORDER BY D.full_name ASC
    ";
    $stmt_doctors = $pdo->prepare($sql_doctors);
    $stmt_doctors->execute([':dept_name' => $selected_department]);
    $doctors_by_department = $stmt_doctors->fetchAll();

    foreach ($doctors_by_department as $key => $doctor) {
        $image_path = $doctor['profile_picture'];
        if (empty($image_path)) {
            $image_path = $default_avatar;
        }
        $doctors_by_department[$key]['image'] = $image_path;
        $doctors_by_department[$key]['rating'] = round(rand(40, 50) / 10, 1);
        // Lấy đoạn mô tả ngắn cho card
        $doctors_by_department[$key]['short_bio'] = substr($doctor['biography'], 0, 100) . '...';
    }
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
        .page-header-banner {
            background-color: #e6f7ff;
            padding: 40px 0;
            margin-bottom: 30px;
            border-bottom: 5px solid #007bff;
        }

        .dept-card,
        .doctor-card {
            border-radius: 12px;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #e0e0e0;
            background-color: #ffffff;
        }

        .dept-card:hover,
        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            line-height: 80px;
            margin: 0 auto 15px;
            border-radius: 50%;
            background-color: #f0f8ff;
        }

        .icon-wrapper .fas {
            font-size: 36px;
        }

        .doctor-avatar {
            width: 120px;
            height: 120px;
            object-fit: cover;
        }

        .text-primary {
            color: #007bff !important;
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <section class="page-header-banner">
            <div class="container">
                <h1 class="fw-bolder text-primary text-center mb-0"><?php echo $page_title; ?></h1>
            </div>
        </section>

        <div class="container py-4">

            <?php if ($selected_department): ?>

                <a href="chuyen-khoa.php" class="btn btn-outline-secondary mb-4"><i class="fas fa-arrow-left me-2"></i>Xem
                    Tất Cả Chuyên Khoa</a>

                <div class="row">
                    <?php if (!empty($doctors_by_department)): ?>
                        <?php foreach ($doctors_by_department as $doctor): ?>
                            <div class="col-12 mb-4">
                                <div class="card doctor-card shadow p-3">
                                    <div class="row g-0">
                                        <div class="col-md-2 text-center">
                                            <img src="<?php echo htmlspecialchars($doctor['image']); ?>"
                                                class="rounded-circle shadow doctor-avatar mb-3 mb-md-0"
                                                alt="<?php echo htmlspecialchars($doctor['full_name']); ?>">
                                        </div>
                                        <div class="col-md-7">
                                            <div class="card-body py-0 py-md-2">
                                                <a href="chi-tiet-bac-si.php?id=<?php echo $doctor['doctor_id']; ?>"
                                                    class="text-decoration-none text-dark">
                                                    <h5 class="card-title fw-bold text-primary">
                                                        <?php echo htmlspecialchars($doctor['full_name']); ?></h5>
                                                </a>
                                                <p class="card-text text-secondary mb-1">Chuyên khoa:
                                                    **<?php echo htmlspecialchars($doctor['department_name']); ?>**</p>
                                                <p class="card-text small text-muted fst-italic mt-2">
                                                    <?php echo htmlspecialchars($doctor['short_bio']); ?></p>
                                            </div>
                                        </div>
                                        <div
                                            class="col-md-3 text-center d-flex flex-column justify-content-center align-items-center">
                                            <p class="text-warning h5 mb-2">
                                                <?php echo render_stars($doctor['rating']); ?>
                                            </p>
                                            <p class="small text-muted mb-3">(<?php echo number_format($doctor['rating'], 1); ?>/5
                                                điểm)</p>

                                            <a href="dat-lich.php?id=<?php echo $doctor['doctor_id']; ?>"
                                                class="btn btn-success w-75 mb-2">
                                                <i class="far fa-calendar-check me-1"></i> Đặt Lịch
                                            </a>
                                            <a href="chi-tiet-bac-si.php?id=<?php echo $doctor['doctor_id']; ?>"
                                                class="btn btn-outline-info w-75">Xem Hồ Sơ</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="alert alert-warning text-center">Hiện chưa có bác sĩ nào đang hoạt động trong chuyên khoa này.
                        </p>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <h2 class="mb-5 fw-bold text-center text-dark">Lựa Chọn Chuyên Khoa Phù Hợp</h2>

                <div class="row">
                    <?php
                    if (!empty($all_departments)):
                        foreach ($all_departments as $dept):
                            $icon_colors = ['text-danger', 'text-info', 'text-success', 'text-primary'];
                            $color_class = $icon_colors[array_rand($icon_colors)];
                            ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                <div class="card dept-card shadow h-100">
                                    <div class="card-body text-center">
                                        <div class="icon-wrapper">
                                            <i
                                                class="fas <?php echo htmlspecialchars($dept['icon_class']); ?> <?php echo $color_class; ?>"></i>
                                        </div>
                                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($dept['department_name']); ?>
                                        </h5>
                                        <p class="card-text small text-muted mb-3">
                                            <?php echo htmlspecialchars(substr($dept['description'], 0, 70)); ?>...</p>

                                        <a href="chuyen-khoa.php?dept=<?php echo urlencode($dept['department_name']); ?>"
                                            class="btn btn-sm btn-primary mt-2">Xem Bác Sĩ</a>
                                    </div>
                                </div>
                            </div>
                        <?php
                        endforeach;
                    else:
                        echo '<p class="alert alert-danger">Không tìm thấy bất kỳ chuyên khoa nào trong hệ thống.</p>';
                    endif;
                    ?>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>