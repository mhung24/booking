<?php require_once 'includes/logic_doctor_list.php'; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/doctor-list.css">
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <section class="page-header-banner">
            <div class="container">
                <h1 class="fw-bolder text-primary text-center mb-0">Đội Ngũ Bác Sĩ</h1>
                <p class="lead text-center text-muted mt-2 mb-0">Tìm kiếm trong số <?php echo $total_doctors; ?> bác sĩ
                    ưu tú.</p>
            </div>
        </section>

        <div class="container py-4">
            <div class="row mb-5 justify-content-center">
                <div class="col-lg-10">
                    <form action="tim-bac-si.php" method="GET" class="p-4 rounded shadow-sm bg-white border">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Chuyên
                                    Khoa</label>
                                <select class="form-select" name="chuyen_khoa" onchange="this.form.submit()">
                                    <option value="">-- Tất cả --</option>
                                    <?php foreach ($all_departments as $dept): ?>
                                        <option value="<?= htmlspecialchars($dept['department_name']); ?>"
                                            <?= ($filter_department == $dept['department_name']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($dept['department_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Tên Bác Sĩ</label>
                                <input type="text" class="form-control" name="ten" placeholder="Nhập tên bác sĩ..."
                                    value="<?= htmlspecialchars($filter_name); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100 fw-bold"><i
                                        class="fas fa-search me-1"></i> Tìm</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <?php if (!empty($doctors_list)): ?>
                    <?php foreach ($doctors_list as $doctor): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card doctor-card shadow-sm p-3">
                                <div class="row g-0 align-items-center">
                                    <div class="col-md-3 text-center">
                                        <img src="<?= htmlspecialchars($doctor['image']); ?>"
                                            class="rounded-circle doctor-avatar mb-3 mb-md-0">
                                    </div>
                                    <div class="col-md-9">
                                        <div class="card-body py-0 ps-md-4">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <a href="chi-tiet-bac-si.php?id=<?= $doctor['doctor_id']; ?>"
                                                        class="text-decoration-none text-dark">
                                                        <h5 class="card-title fw-bold text-primary mb-1">
                                                            <?= htmlspecialchars($doctor['full_name']); ?>
                                                        </h5>
                                                    </a>
                                                    <span
                                                        class="badge bg-light text-secondary border mb-2"><?= htmlspecialchars($doctor['department_name']); ?></span>
                                                </div>
                                                <div class="text-end">
                                                    <div class="text-warning small">
                                                        <?= render_stars((float) $doctor['rating']); ?>
                                                    </div>
                                                    <small class="text-muted"
                                                        style="font-size: 0.75rem;">(<?= $doctor['total_reviews']; ?> đánh
                                                        giá)</small>
                                                </div>
                                            </div>
                                            <p class="card-text small text-muted fst-italic mt-2 mb-3 border-bottom pb-2">
                                                <?= htmlspecialchars($doctor['short_bio']); ?>
                                            </p>
                                            <div class="d-flex gap-2">
                                                <a href="chi-tiet-bac-si.php?id=<?= $doctor['doctor_id']; ?>"
                                                    class="btn btn-sm btn-outline-secondary flex-grow-1">Xem Hồ Sơ</a>
                                                <?php
                                                $link = $is_logged_in ? "dat-lich.php?id=" . $doctor['doctor_id'] : "login.php?redirect=" . urlencode("dat-lich.php?id=" . $doctor['doctor_id']);
                                                $btn_cls = $is_logged_in ? "btn-success" : "btn-primary";
                                                $btn_txt = $is_logged_in ? "Đặt Lịch" : "Đăng Nhập Đặt Lịch";
                                                ?>
                                                <a href="<?= $link; ?>"
                                                    class="btn btn-sm <?= $btn_cls; ?> flex-grow-1 fw-bold"><?= $btn_txt; ?></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center p-5 shadow-sm">
                            <h4>Không tìm thấy bác sĩ nào!</h4>
                            <a href="tim-bac-si.php" class="btn btn-outline-dark mt-3">Xóa bộ lọc</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="row mt-4">
                    <div class="col-12 d-flex justify-content-center">
                        <nav>
                            <ul class="pagination shadow-sm">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                        href="?p=<?= $page - 1; ?>&chuyen_khoa=<?= urlencode($filter_department); ?>&ten=<?= urlencode($filter_name); ?>"><i
                                            class="fas fa-chevron-left"></i></a>
                                </li>
                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);
                                if ($start > 1)
                                    echo '<li class="page-item"><a class="page-link" href="?p=1">1</a></li><li class="page-item disabled"><span class="page-link">...</span></li>';
                                for ($i = $start; $i <= $end; $i++):
                                    ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link"
                                            href="?p=<?= $i; ?>&chuyen_khoa=<?= urlencode($filter_department); ?>&ten=<?= urlencode($filter_name); ?>"><?= $i; ?></a>
                                    </li>
                                <?php endfor;
                                if ($end < $total_pages)
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li><li class="page-item"><a class="page-link" href="?p=' . $total_pages . '">' . $total_pages . '</a></li>';
                                ?>
                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                        href="?p=<?= $page + 1; ?>&chuyen_khoa=<?= urlencode($filter_department); ?>&ten=<?= urlencode($filter_name); ?>"><i
                                            class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>