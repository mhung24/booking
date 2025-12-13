<?php
// Giả sử file này được nhúng vào index.php hoặc trang nào đó đã require connect.php
if (!isset($pdo)) {
    require_once 'config/connect.php';
}

global $pdo;

// Truy vấn để lấy các phản hồi có điểm cao và nội dung không rỗng
$sql_reviews = "
    SELECT 
        patient_name, 
        review_text, 
        rating_score, 
        T.department_name
    FROM Ratings R
    JOIN Doctors D ON R.doctor_id = D.doctor_id
    JOIN Departments T ON D.department_id = T.department_id
    WHERE R.review_text IS NOT NULL AND R.review_text != ''
    ORDER BY R.rating_score DESC, R.rating_date DESC -- Ưu tiên điểm cao và mới nhất
    LIMIT 4 -- Lấy 4 đánh giá để linh hoạt hiển thị 2 hoặc 4 cột
";
$stmt_reviews = $pdo->prepare($sql_reviews);
$stmt_reviews->execute();
$reviews = $stmt_reviews->fetchAll();

// Fallback data nếu database trống
if (empty($reviews)) {
    $reviews = [
        [
            'patient_name' => 'Nguyễn Thị H.',
            'review_text' => 'Hệ thống đặt lịch rất tiện lợi, tôi không còn phải chờ đợi lâu như trước nữa. Bác sĩ tư vấn rất nhiệt tình!',
            'department_name' => 'Nhi khoa',
            'rating_score' => 5.0,
        ],
        [
            'patient_name' => 'Phạm Văn K.',
            'review_text' => 'Việc quản lý hồ sơ trực tuyến giúp tôi theo dõi lịch sử khám bệnh dễ dàng. Dịch vụ tuyệt vời và chuyên nghiệp!',
            'department_name' => 'Tim Mạch',
            'rating_score' => 5.0,
        ]
    ];
}

// Hàm render_stars (Giữ lại để đảm bảo hoạt động)
function render_stars_small(float $rating): string
{
    $html = '';
    $full_stars = floor($rating);
    for ($i = 0; $i < 5; $i++) {
        $color = ($i < $full_stars) ? 'text-warning' : 'text-secondary';
        $html .= '<i class="fas fa-star ' . $color . ' small"></i>';
    }
    return $html;
}

?>

<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold">Phản Hồi Từ Khách Hàng</h2>
        <div class="row justify-content-center">

            <?php foreach ($reviews as $index => $review): ?>
                <?php
                $border_color = ($index % 2 == 0) ? 'border-primary' : 'border-success';
                ?>

                <div class="col-md-6 mb-4">
                    <blockquote
                        class="blockquote bg-white p-4 rounded shadow-sm border-start <?php echo $border_color; ?> border-5 h-100 d-flex flex-column">

                        <div class="mb-2">
                            <?php echo render_stars_small((float) $review['rating_score']); ?>
                        </div>

                        <p class="mb-0 fst-italic text-muted review-text flex-grow-1">
                            "<?php echo nl2br(htmlspecialchars($review['review_text'])); ?>"
                        </p>

                        <footer class="blockquote-footer mt-3">
                            <?php echo htmlspecialchars($review['patient_name']); ?>
                            <cite title="Chuyên Khoa">Bệnh nhân
                                <?php echo htmlspecialchars($review['department_name']); ?></cite>
                        </footer>
                    </blockquote>
                </div>
            <?php endforeach; ?>

            <?php if (empty($reviews)): ?>
                <div class="col-12 text-center">
                    <p class="alert alert-info">Chưa có phản hồi nào để hiển thị.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<style>
    .review-text {
        font-size: 1rem;
    }
</style>