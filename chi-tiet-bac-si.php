<?php
require_once 'config/connect.php';

// Khởi tạo session và kiểm tra trạng thái đăng nhập
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'Khách';

// === KIỂM TRA ID BÁC SĨ ===
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tim-bac-si.php");
    exit();
}

$doctor_id = (int) $_GET['id'];
$default_avatar = './img/no_avatar.png';

global $pdo;

// Hàm render_stars 
function render_stars(float $rating): string
{
    $html = '';
    $full_stars = floor($rating);
    $has_half = ($rating - $full_stars) >= 0.5;
    $empty_stars = 5 - ceil($rating);

    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '<i class="fas fa-star text-warning"></i>';
    }
    if ($has_half) {
        $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '<i class="far fa-star text-warning"></i>';
    }
    return $html;
}

// === 1. TRUY VẤN THÔNG TIN BÁC SĨ ===
$sql_doctor = "
    SELECT 
        D.doctor_id, D.full_name, D.profile_picture, D.biography, D.experience_years, 
        D.education, D.working_location, T.department_name, T.department_id,
        COALESCE(AVG(R.rating_score), 0) AS average_rating,
        COUNT(R.rating_id) AS total_reviews
    FROM Doctors D
    JOIN Departments T ON D.department_id = T.department_id
    LEFT JOIN Ratings R ON D.doctor_id = R.doctor_id
    WHERE D.doctor_id = :id AND D.status = 'ACTIVE'
    GROUP BY D.doctor_id, D.full_name, D.profile_picture, D.biography, D.experience_years, D.education, D.working_location, T.department_name, T.department_id
";
$stmt_doctor = $pdo->prepare($sql_doctor);
$stmt_doctor->execute([':id' => $doctor_id]);
$doctor = $stmt_doctor->fetch();

if (!$doctor) {
    header("Location: tim-bac-si.php");
    exit();
}

$doctor['rating'] = number_format((float) $doctor['average_rating'], 1);
$doctor['image'] = !empty($doctor['profile_picture']) ? $doctor['profile_picture'] : $default_avatar;

// === 2. TRUY VẤN ĐÁNH GIÁ (REVIEWS) ===
$sql_reviews = "
    SELECT patient_name, rating_score, review_text, rating_date
    FROM Ratings
    WHERE doctor_id = :id
    ORDER BY rating_date DESC
";
$stmt_reviews = $pdo->prepare($sql_reviews);
$stmt_reviews->execute([':id' => $doctor_id]);
$reviews = $stmt_reviews->fetchAll();

// === 3. XỬ LÝ FORM ĐÁNH GIÁ (Nếu form được submit) ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    if (!$is_logged_in) {
        $error_review = "Vui lòng đăng nhập để gửi đánh giá.";
    } else {
        $rating_score = (int) $_POST['rating_score'];
        $review_text = trim($_POST['review_text']);
        $patient_name = $_SESSION['user_name']; // Lấy tên từ session

        if ($rating_score < 1 || $rating_score > 5) {
            $error_review = "Điểm đánh giá không hợp lệ.";
        } else {
            try {
                $sql_insert_review = "
                    INSERT INTO Ratings (doctor_id, patient_id, patient_name, rating_score, review_text)
                    VALUES (:doctor_id, :patient_id, :patient_name, :rating_score, :review_text)
                ";
                $stmt_insert_review = $pdo->prepare($sql_insert_review);
                $stmt_insert_review->execute([
                    ':doctor_id' => $doctor_id,
                    ':patient_id' => $user_id,
                    ':patient_name' => $patient_name,
                    ':rating_score' => $rating_score,
                    ':review_text' => $review_text
                ]);
                $success_review = "Cảm ơn bạn đã gửi đánh giá!";
                header("Location: chi-tiet-bac-si.php?id=" . $doctor_id);
                exit();
            } catch (PDOException $e) {
                $error_review = "Lỗi khi gửi đánh giá: " . $e->getMessage();
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
    <title>Hồ Sơ Bác Sĩ: <?php echo htmlspecialchars($doctor['full_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .doctor-header {
            background-color: #f7f9fc;
            padding: 40px 0;
            border-bottom: 3px solid #007bff;
        }

        .doctor-avatar-lg {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .text-primary {
            color: #007bff !important;
        }

        .text-success {
            color: #198754 !important;
        }

        .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #007bff;
            color: #007bff !important;
        }

        .review-card {
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .booking-btn-cta {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 8px;
        }

        .booking-btn-cta:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(25, 135, 84, 0.4);
        }

        .rating-stars label {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
        }

        .rating-stars input:checked~label,
        .rating-stars label:hover,
        .rating-stars label:hover~label {
            color: #ffc107;
        }

        .rating-stars input {
            display: none;
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <section class="doctor-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        <img src="<?php echo htmlspecialchars($doctor['image']); ?>"
                            class="rounded-circle doctor-avatar-lg"
                            alt="<?php echo htmlspecialchars($doctor['full_name']); ?>">
                    </div>
                    <div class="col-md-6">
                        <h1 class="fw-bolder text-primary"><?php echo htmlspecialchars($doctor['full_name']); ?></h1>
                        <p class="lead text-success fw-bold"><i class="fas fa-stethoscope me-2"></i>
                            <?php echo htmlspecialchars($doctor['department_name']); ?></p>
                        <p class="text-muted mb-1">
                            <i class="fas fa-map-marker-alt me-2"></i> **Địa điểm làm việc:**
                            <?php echo htmlspecialchars($doctor['working_location'] ?? 'Đang cập nhật'); ?>
                        </p>
                        <p class="text-warning h5 mt-3 mb-1">
                            <?php echo render_stars((float) $doctor['rating']); ?>
                            <span class="small text-dark fw-normal ms-2">(<?php echo $doctor['rating']; ?>/5 từ
                                <?php echo $doctor['total_reviews']; ?> đánh giá)</span>
                        </p>
                    </div>

                    <div class="col-md-3 mt-4 mt-md-0 d-grid gap-2">
                        <?php
                        // LOGIC NÚT ĐẶT LỊCH (Chuyển hướng đến Login nếu chưa đăng nhập)
                        if ($is_logged_in) {
                            $booking_link = "dat-lich.php?id=" . $doctor_id;
                            $button_class = "btn-success";
                            $button_text = "ĐẶT LỊCH NGAY";
                        } else {
                            $redirect_url = urlencode("dat-lich.php?id=" . $doctor_id);
                            $booking_link = "login.php?redirect=" . $redirect_url;
                            $button_class = "btn-outline-primary";
                            $button_text = "ĐĂNG NHẬP ĐỂ ĐẶT LỊCH";
                        }
                        ?>
                        <a href="<?php echo $booking_link; ?>"
                            class="btn <?php echo $button_class; ?> btn-lg fw-bold booking-btn-cta shadow text-uppercase">
                            <i class="far fa-calendar-check me-2"></i> <?php echo $button_text; ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="container py-5">
            <div class="row">
                <div class="col-md-12">
                    <ul class="nav nav-tabs mb-4" id="doctorTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab"
                                data-bs-target="#profile" type="button" role="tab">Hồ Sơ & Chuyên Môn</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule"
                                type="button" role="tab">Lịch Làm Việc</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews"
                                type="button" role="tab">Đánh Giá (<?php echo $doctor['total_reviews']; ?>)</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="doctorTabsContent">

                        <div class="tab-pane fade show active" id="profile" role="tabpanel"
                            aria-labelledby="profile-tab">
                            <div class="p-4 bg-white shadow-sm rounded">
                                <h4 class="text-primary fw-bold mb-3">Thông Tin Chung</h4>
                                <ul class="list-group list-group-flush mb-4">
                                    <li class="list-group-item"><i class="fas fa-calendar-alt me-2 text-success"></i>
                                        **Kinh nghiệm:**
                                        <?php echo htmlspecialchars($doctor['experience_years'] ?? 'Chưa rõ'); ?> năm
                                    </li>
                                    <li class="list-group-item"><i class="fas fa-graduation-cap me-2 text-success"></i>
                                        **Học vấn/Bằng cấp:**
                                        <?php echo htmlspecialchars($doctor['education'] ?? 'Đang cập nhật'); ?>
                                    </li>
                                </ul>

                                <h4 class="text-primary fw-bold mb-3">Tiểu Sử & Lĩnh vực chuyên sâu</h4>
                                <div class="p-3 border rounded bg-light">
                                    <p class="mb-0">
                                        <?php echo nl2br(htmlspecialchars($doctor['biography'] ?? 'Chưa có thông tin tiểu sử chi tiết.')); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
                            <div class="p-4 bg-white shadow-sm rounded">
                                <h4 class="text-primary fw-bold mb-3">Lịch Làm Việc (Dự kiến)</h4>
                                <p class="text-muted">Thông tin này chỉ mang tính tham khảo. Vui lòng xác nhận lịch hẹn
                                    qua hệ thống hoặc nhân viên y tế.</p>

                                <table class="table table-striped table-hover mt-3">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Ngày</th>
                                            <th>Ca Sáng</th>
                                            <th>Ca Chiều</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Thứ Hai</td>
                                            <td>08:00 - 12:00</td>
                                            <td>13:30 - 17:00</td>
                                        </tr>
                                        <tr>
                                            <td>Thứ Ba</td>
                                            <td>08:00 - 12:00</td>
                                            <td>13:30 - 17:00</td>
                                        </tr>
                                        <tr>
                                            <td>Thứ Tư</td>
                                            <td>08:00 - 12:00</td>
                                            <td>13:30 - 17:00</td>
                                        </tr>
                                        <tr>
                                            <td>Thứ Năm</td>
                                            <td>08:00 - 12:00</td>
                                            <td>13:30 - 17:00</td>
                                        </tr>
                                        <tr>
                                            <td>Thứ Sáu</td>
                                            <td>08:00 - 12:00</td>
                                            <td>13:30 - 17:00</td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td>Thứ Bảy</td>
                                            <td>08:00 - 11:30</td>
                                            <td>Nghỉ</td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td>Chủ Nhật</td>
                                            <td>Nghỉ</td>
                                            <td>Nghỉ</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                            <div class="p-4 bg-white shadow-sm rounded">
                                <h4 class="text-primary fw-bold mb-3">Gửi Đánh Giá Của Bạn</h4>

                                <?php if (isset($error_review)): ?>
                                    <div class="alert alert-danger"><?php echo $error_review; ?></div>
                                <?php endif; ?>
                                <?php if (isset($success_review)): ?>
                                    <div class="alert alert-success"><?php echo $success_review; ?></div>
                                <?php endif; ?>

                                <?php if ($is_logged_in): ?>
                                    <form action="chi-tiet-bac-si.php?id=<?php echo $doctor_id; ?>" method="POST"
                                        class="mb-5">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Điểm đánh giá:</label>
                                            <div class="rating-stars">
                                                <input type="radio" id="star5" name="rating_score" value="5" required><label
                                                    for="star5" title="Tuyệt vời"><i class="fas fa-star"></i></label>
                                                <input type="radio" id="star4" name="rating_score" value="4"><label
                                                    for="star4" title="Rất tốt"><i class="fas fa-star"></i></label>
                                                <input type="radio" id="star3" name="rating_score" value="3"><label
                                                    for="star3" title="Bình thường"><i class="fas fa-star"></i></label>
                                                <input type="radio" id="star2" name="rating_score" value="2"><label
                                                    for="star2" title="Không tốt"><i class="fas fa-star"></i></label>
                                                <input type="radio" id="star1" name="rating_score" value="1"><label
                                                    for="star1" title="Tệ"><i class="fas fa-star"></i></label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="review_text" class="form-label fw-bold">Nội dung phản hồi:</label>
                                            <textarea class="form-control" id="review_text" name="review_text" rows="3"
                                                placeholder="Chia sẻ trải nghiệm của bạn (Không bắt buộc)"></textarea>
                                        </div>
                                        <button type="submit" name="submit_review" class="btn btn-primary fw-bold">Gửi Đánh
                                            Giá</button>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-warning text-center">
                                        Vui lòng <a
                                            href="login.php?redirect=<?php echo urlencode('chi-tiet-bac-si.php?id=' . $doctor_id); ?>"
                                            class="alert-link">Đăng nhập</a> để gửi đánh giá về bác sĩ này.
                                    </div>
                                <?php endif; ?>

                                <h4 class="text-success fw-bold border-bottom pb-2 mt-5">Các Đánh Giá Gần Đây</h4>

                                <?php if (!empty($reviews)): ?>
                                    <?php foreach ($reviews as $review): ?>
                                        <div class="review-card p-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <p class="fw-bold mb-0 text-dark">
                                                    <?php echo htmlspecialchars($review['patient_name']); ?>
                                                </p>
                                                <small
                                                    class="text-muted"><?php echo date('d/m/Y', strtotime($review['rating_date'])); ?></small>
                                            </div>
                                            <p class="text-warning small my-1">
                                                <?php echo render_stars((float) $review['rating_score']); ?>
                                                <span
                                                    class="text-dark small ms-1">(<?php echo htmlspecialchars($review['rating_score']); ?>/5)</span>
                                            </p>
                                            <p class="mb-0 text-muted fst-italic">
                                                <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center">Chưa có đánh giá nào cho bác sĩ này.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>