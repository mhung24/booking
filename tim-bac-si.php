<?php
require_once 'config/connect.php'; 

// Khởi tạo session và kiểm tra trạng thái đăng nhập
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Hàm render_stars (Tạm thời để trong file nếu chưa chuyển sang helpers)
function render_stars(float $rating): string {
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


$page_title = "Xem Toàn Bộ Đội Ngũ Bác Sĩ";
$default_avatar = './img/no_avatar.png';

$filter_department = $_GET['chuyen_khoa'] ?? ''; 
$filter_name = $_GET['ten'] ?? ''; 

global $pdo;

// === PHÂN TRANG: THIẾT LẬP BIẾN ===
$limit = 25; 
$page = $_GET['p'] ?? 1; 
$page = max(1, (int)$page); 
$offset = ($page - 1) * $limit; 

$stmt_all_departments = $pdo->prepare("SELECT department_name FROM Departments ORDER BY department_name ASC");
$stmt_all_departments->execute();
$all_departments = $stmt_all_departments->fetchAll();


// === 1. ĐẾM TỔNG SỐ BÁC SĨ ===
$sql_count = "
    SELECT COUNT(D.doctor_id) AS total_doctors
    FROM Doctors D
    JOIN Departments T ON D.department_id = T.department_id
    WHERE D.status = 'ACTIVE'
";

$count_params = [];
if (!empty($filter_department)) {
    $sql_count .= " AND T.department_name = :dept_name";
    $count_params[':dept_name'] = $filter_department;
}
if (!empty($filter_name)) {
    $sql_count .= " AND D.full_name LIKE :full_name";
    $count_params[':full_name'] = '%' . $filter_name . '%';
}

$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($count_params);
$total_doctors = $stmt_count->fetchColumn(); 
$total_pages = ceil($total_doctors / $limit); 


// === 2. TRUY VẤN DANH SÁCH BÁC SĨ ===
$sql_doctors = "
    SELECT 
        D.doctor_id, D.full_name, D.profile_picture, D.biography, T.department_name,
        COALESCE(AVG(R.rating_score), 0) AS average_rating,
        COUNT(R.rating_id) AS total_reviews
    FROM Doctors D
    JOIN Departments T ON D.department_id = T.department_id
    LEFT JOIN Ratings R ON D.doctor_id = R.doctor_id
    WHERE D.status = 'ACTIVE' 
";

$params = $count_params; 

$sql_doctors .= " 
    GROUP BY D.doctor_id, D.full_name, D.profile_picture, D.biography, T.department_name
    ORDER BY D.full_name ASC
    LIMIT :limit OFFSET :offset 
";

$params[':limit'] = $limit;
$params[':offset'] = $offset;

$stmt_doctors = $pdo->prepare($sql_doctors);

foreach ($count_params as $key => &$value) {
    $stmt_doctors->bindParam($key, $value);
}

$stmt_doctors->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt_doctors->bindParam(':offset', $offset, PDO::PARAM_INT);

$stmt_doctors->execute();
$doctors_list = $stmt_doctors->fetchAll();


foreach ($doctors_list as $key => $doctor) {
    $doctors_list[$key]['rating'] = number_format((float)$doctor['average_rating'], 1);
    $doctors_list[$key]['total_reviews'] = (int)$doctor['total_reviews'];
    
    $image_path = $doctor['profile_picture'];
    if (empty($image_path)) {
        $image_path = $default_avatar;
    }
    $doctors_list[$key]['image'] = $image_path;
    
    $doctors_list[$key]['short_bio'] = substr($doctor['biography'] ?? 'Chưa có mô tả.', 0, 120) . '...';
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
        .doctor-card {
            border-radius: 12px;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #e0e0e0;
            background-color: #ffffff;
        }
        .doctor-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        .doctor-avatar {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .text-primary {
            color: #007bff !important;
        }
        
        /* CSS cho nút Đặt Lịch (Cải tiến) */
        .booking-btn-cta {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 8px;
        }
        .booking-btn-cta:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(25, 135, 84, 0.4); 
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<main>
    <section class="page-header-banner">
        <div class="container">
            <h1 class="fw-bolder text-primary text-center mb-0">Đội Ngũ Bác Sĩ Chuyên Nghiệp</h1>
            <p class="lead text-center text-muted mt-2 mb-0">Tìm kiếm <?php echo $total_doctors; ?> bác sĩ đang hoạt động theo chuyên khoa hoặc tên.</p>
        </div>
    </section>

    <div class="container py-4">
        
        <div class="row mb-5 justify-content-center">
            <div class="col-md-10">
                <form action="tim-bac-si.php" method="GET" class="row g-3 align-items-end p-4 rounded shadow-sm bg-white" id="doctor-filter-form">
                    
                    <div class="col-md-5">
                        <label for="chuyen_khoa_filter" class="form-label fw-bold">Lọc theo Chuyên Khoa:</label>
                        <select class="form-select" id="chuyen_khoa_filter" name="chuyen_khoa" onchange="this.form.submit()">
                            <option value="">-- Tất cả Chuyên khoa --</option>
                            <?php foreach ($all_departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept['department_name']); ?>"
                                    <?php echo ($filter_department == $dept['department_name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-7">
                        <label for="search_name" class="form-label fw-bold">Tìm theo Tên Bác Sĩ:</label>
                        <input type="text" 
                               class="form-control" 
                               id="search_name" 
                               name="ten" 
                               placeholder="Nhập tên bác sĩ..."
                               value="<?php echo htmlspecialchars($filter_name); ?>">
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row">
            <?php if (!empty($doctors_list)): ?>
                <?php foreach ($doctors_list as $doctor): ?>
                
                <div class="col-12 mb-4">
                    <div class="card doctor-card shadow p-3">
                        <div class="row g-0 align-items-center">
                            
                            <div class="col-md-2 text-center">
                                <img src="<?php echo htmlspecialchars($doctor['image']); ?>" 
                                     class="rounded-circle shadow doctor-avatar mb-2 mb-md-0"
                                     alt="<?php echo htmlspecialchars($doctor['full_name']); ?>">
                            </div>
                            
                            <div class="col-md-7">
                                <div class="card-body py-0 py-md-2">
                                    <a href="chi-tiet-bac-si.php?id=<?php echo $doctor['doctor_id']; ?>" class="text-decoration-none text-dark">
                                        <h5 class="card-title fw-bold text-primary mb-1"><?php echo htmlspecialchars($doctor['full_name']); ?></h5>
                                    </a>
                                    <p class="card-text text-secondary mb-1">Chuyên khoa: **<?php echo htmlspecialchars($doctor['department_name']); ?>**</p>
                                    <p class="card-text small text-muted fst-italic mt-2"><?php echo htmlspecialchars($doctor['short_bio']); ?></p>
                                </div>
                            </div>
                            
                            <div class="col-md-3 text-center d-flex flex-column justify-content-center align-items-center">
                                <p class="text-warning h5 mb-2">
                                    <?php echo render_stars((float)$doctor['rating']); ?>
                                </p>
                                <p class="small text-muted mb-3">(<?php echo $doctor['rating']; ?>/5 điểm từ <?php echo $doctor['total_reviews']; ?> đánh giá)</p>

                                <?php 
                                // LOGIC KIỂM TRA ĐĂNG NHẬP CHO NÚT ĐẶT LỊCH
                                $doctor_id = $doctor['doctor_id']; 

                                if ($is_logged_in) {
                                    // TRƯỜNG HỢP 1: Đã Đăng Nhập
                                    $booking_link = "dat-lich.php?id=" . $doctor_id;
                                    $button_class = "btn-success"; 
                                    $button_text = "ĐẶT LỊCH NGAY";
                                } else {
                                    // TRƯỜNG HỢP 2: Chưa Đăng Nhập
                                    $redirect_url = urlencode("dat-lich.php?id=" . $doctor_id);
                                    $booking_link = "login.php?redirect=" . $redirect_url;
                                    $button_class = "btn-outline-primary"; 
                                    $button_text = "ĐĂNG NHẬP ĐỂ ĐẶT LỊCH";
                                }
                                ?>
                                
                                <a href="<?php echo $booking_link; ?>" 
                                   class="btn <?php echo $button_class; ?> w-75 mb-2 fw-bold booking-btn-cta shadow-sm text-uppercase">
                                    <i class="far fa-calendar-check me-1"></i> <?php echo $button_text; ?>
                                </a>

                                <a href="chi-tiet-bac-si.php?id=<?php echo $doctor['doctor_id']; ?>" class="btn btn-outline-info w-75">Xem Hồ Sơ</a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endforeach; ?>
            <?php else: ?>
                <p class="alert alert-warning text-center col-12">Không tìm thấy bác sĩ nào theo tiêu chí tìm kiếm hoặc bộ lọc.</p>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="row mt-4">
            <div class="col-12 d-flex justify-content-center">
                <nav aria-label="Phân trang danh sách bác sĩ">
                    <ul class="pagination">
                        
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?p=<?php echo $page - 1; ?>&chuyen_khoa=<?php echo urlencode($filter_department); ?>&ten=<?php echo urlencode($filter_name); ?>">Trước</a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?p=<?php echo $i; ?>&chuyen_khoa=<?php echo urlencode($filter_department); ?>&ten=<?php echo urlencode($filter_name); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?p=<?php echo $page + 1; ?>&chuyen_khoa=<?php echo urlencode($filter_department); ?>&ten=<?php echo urlencode($filter_name); ?>">Sau</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('doctor-filter-form');
        const searchNameInput = document.getElementById('search_name');

        const debounce = (func, delay) => {
            let timeoutId;
            return function(...args) {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    func.apply(this, args);
                }, delay);
            };
        };

        const performSearch = () => {
            form.submit();
        };

        searchNameInput.addEventListener('input', debounce(performSearch, 300));
    });
</script>
</body>
</html>