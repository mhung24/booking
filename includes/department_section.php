<?php

$stmt_departments = $pdo->prepare("SELECT department_name, icon_class FROM Departments ORDER BY department_id ASC LIMIT 4");
$stmt_departments->execute();
$popular_departments = $stmt_departments->fetchAll();

$icon_colors = ['text-danger', 'text-info', 'text-warning', 'text-success'];
?>

<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold">Các Chuyên Khoa Phổ Biến</h2>
        <div class="row">

            <?php
            if (!empty($popular_departments)):
                foreach ($popular_departments as $dept):
                    $color_class = $icon_colors[array_rand($icon_colors)];
                    ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card text-center border-0 shadow">
                            <div class="card-body">
                                <i
                                    class="fas <?php echo htmlspecialchars($dept['icon_class']); ?> fa-4x <?php echo $color_class; ?> my-3"></i>
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($dept['department_name']); ?></h5>
                                <a href="chuyen-khoa.php?dept=<?php echo urlencode($dept['department_name']); ?>"
                                    class="btn btn-sm btn-outline-primary mt-2">Xem Chi Tiết</a>
                            </div>
                        </div>
                    </div>
                    <?php
                endforeach;
            else:
                echo '<p class="text-center">Hiện chưa có chuyên khoa nào được thêm vào hệ thống.</p>';
            endif;
            ?>

        </div>

        <div class="text-center mt-4">
            <a href="chuyen-khoa.php" class="btn btn-lg btn-outline-secondary">Xem Tất Cả Chuyên Khoa</a>
        </div>
    </div>
</section>