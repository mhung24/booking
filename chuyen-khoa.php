<?php require_once 'includes/logic_chuyen_khoa.php'; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="css/chuyen_khoa.css" rel="stylesheet">
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
                                                        <?php echo htmlspecialchars($doctor['full_name']); ?>
                                                    </h5>
                                                </a>
                                                <p class="card-text text-secondary mb-1">Chuyên khoa:
                                                    **<?php echo htmlspecialchars($doctor['department_name']); ?>**</p>
                                                <p class="card-text small text-muted fst-italic mt-2">
                                                    <?php echo htmlspecialchars($doctor['short_bio']); ?>
                                                </p>
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
                                            <?php echo htmlspecialchars(substr($dept['description'], 0, 70)); ?>...
                                        </p>

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