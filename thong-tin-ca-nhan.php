<?php require_once 'includes/logic_profile.php'; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="css/profile.css" rel="stylesheet">
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <div class="container py-5">
            <div class="profile-container">
                <h1 class="fw-bolder text-center mb-5 text-primary">
                    <i class="fas fa-cog me-2"></i> <?php echo $page_title; ?>
                </h1>

                <?php if ($success_message): ?>
                    <div class="alert alert-success text-center"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="row mb-5">
                    <div class="col-md-6 info-group">
                        <label class="form-label fw-bold">Họ và Tên:</label>
                        <p class="lead mb-0 text-dark"><?php echo htmlspecialchars($patient['full_name']); ?></p>
                    </div>
                    <div class="col-md-6 info-group">
                        <label class="form-label fw-bold">Số Điện Thoại:</label>
                        <p class="lead mb-0 text-dark"><?php echo htmlspecialchars($patient['phone_number']); ?></p>
                    </div>
                    <div class="col-12 mt-3">
                        <p class="small text-muted fst-italic">Họ tên và Số điện thoại là thông tin định danh, không thể
                            thay đổi tại đây.</p>
                    </div>
                </div>

                <form action="thong-tin-ca-nhan.php" method="POST">
                    <h4 class="form-title">Cập Nhật Thông Tin Sức Khỏe</h4>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="gender" class="form-label fw-bold"><i
                                    class="fas fa-venus-mars me-2 text-primary"></i> Giới Tính</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">-- Chọn Giới Tính --</option>
                                <option value="Male" <?php echo ($current_gender == 'Male') ? 'selected' : ''; ?>>Nam
                                </option>
                                <option value="Female" <?php echo ($current_gender == 'Female') ? 'selected' : ''; ?>>Nữ
                                </option>
                                <option value="Other" <?php echo ($current_gender == 'Other') ? 'selected' : ''; ?>>Khác
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label fw-bold"><i
                                    class="fas fa-birthday-cake me-2 text-primary"></i> Ngày Sinh</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                value="<?php echo htmlspecialchars($current_dob ?? ''); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-bold"><i
                                    class="fas fa-envelope me-2 text-primary"></i> Email (Đăng ký)</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo $current_email; ?>" placeholder="VD: user@example.com">
                        </div>

                        <div class="col-md-12">
                            <label for="address" class="form-label fw-bold"><i
                                    class="fas fa-map-marker-alt me-2 text-primary"></i> Địa Chỉ</label>
                            <input type="text" class="form-control" id="address" name="address"
                                value="<?php echo $current_address; ?>" placeholder="VD: Số nhà, Tên đường, Quận/Huyện">
                        </div>

                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" name="update_profile" class="btn btn-success btn-lg fw-bold">
                            <i class="fas fa-save me-2"></i> LƯU THAY ĐỔI
                        </button>
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <a href="lich-hen.php" class="btn btn-outline-secondary">
                        <i class="far fa-calendar-check me-2"></i> Quay lại Lịch Hẹn
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>