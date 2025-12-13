<?php
// Đảm bảo session đã được khởi tạo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

global $pdo;

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

$default_avatar = './img/no_avatar.png';
$temp_ratings = [4.8, 5.0, 4.5];


$sql_doctors = "
    SELECT 
        D.doctor_id,        -- ĐÃ THÊM: Lấy doctor_id
        D.full_name, 
        D.profile_picture,   
        T.department_name
    FROM Doctors D
    JOIN Departments T ON D.department_id = T.department_id
    WHERE D.status = 'ACTIVE'
    LIMIT 3
";
$stmt_doctors = $pdo->prepare($sql_doctors);
$stmt_doctors->execute();
$featured_doctors = $stmt_doctors->fetchAll();

foreach ($featured_doctors as $key => $doctor) {
    // Tạm thời gán rating ngẫu nhiên nếu không lấy được từ DB (nên lấy từ bảng Ratings thật)
    $featured_doctors[$key]['rating'] = $temp_ratings[$key] ?? 4.0;

    $image_path = $doctor['profile_picture'];
    if (empty($image_path)) {
        $image_path = $default_avatar;
    }
    $featured_doctors[$key]['image'] = $image_path;
}
?>

<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold text-primary">Đội Ngũ Bác Sĩ Tiêu Biểu</h2>
        <div class="row">

            <?php
            if (!empty($featured_doctors)):
                foreach ($featured_doctors as $doctor):

                    $doctor_id = $doctor['doctor_id'];
                    $target_page = "dat-lich.php?id=" . $doctor_id;

                    if (!$is_logged_in) {
                        // Chưa đăng nhập: Chuyển hướng đến Login
                        $redirect_url = urlencode($target_page);
                        $final_link = "login.php?redirect=" . $redirect_url;
                        $button_text = "Đăng Nhập để Đặt Lịch";
                        $button_class = "btn-outline-primary";
                    } else {
                        // Đã đăng nhập: Chuyển thẳng đến trang đặt lịch
                        $final_link = $target_page;
                        $button_text = "Đặt Lịch";
                        $button_class = "btn-primary";
                    }
                    ?>

                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm text-center h-100">
                            <img src="<?php echo htmlspecialchars($doctor['image']); ?>"
                                class="card-img-top mx-auto mt-3 rounded-circle"
                                alt="<?php echo htmlspecialchars($doctor['full_name']); ?>"
                                style="width: 150px; height: 150px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($doctor['full_name']); ?></h5>
                                <p class="card-text text-secondary">Chuyên khoa:
                                    **<?php echo htmlspecialchars($doctor['department_name']); ?>**</p>
                                <p class="text-warning">
                                    <?php echo render_stars($doctor['rating']); ?>
                                    (<?php echo number_format($doctor['rating'], 1); ?>/5)
                                </p>

                                <a href="<?php echo $final_link; ?>"
                                    class="btn btn-sm <?php echo $button_class; ?> mt-auto fw-bold">
                                    <i class="far fa-calendar-check me-1"></i> <?php echo $button_text; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                endforeach;
            else:
                echo '<p class="text-center">Hiện chưa có bác sĩ nào đang hoạt động trong hệ thống.</p>';
            endif;
            ?>

        </div>
        <div class="text-center mt-4">
            <a href="tim-bac-si.php" class="btn btn-lg btn-success fw-bold">XEM TOÀN BỘ ĐỘI NGŨ BÁC SĨ</a>
        </div>
    </div>
</section>