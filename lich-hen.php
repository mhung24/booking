<?php require_once 'includes/logic_lich_hen.php'; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="css/lich_hen.css" rel="stylesheet">
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <div class="container py-5">
            <h1 class="fw-bolder text-center mb-5 text-primary-custom">
                <i class="far fa-calendar-check me-2"></i> <?php echo $page_title; ?>
            </h1>

            <?php if (!empty($cancel_message)): ?>
                <div class="alert alert-<?php echo $cancel_message['type']; ?> text-center mb-4">
                    <?php echo $cancel_message['text']; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($appointments)): ?>
                <div class="row">
                    <?php foreach ($appointments as $app): ?>
                        <div class="col-lg-12 mb-4">
                            <div class="card appointment-card shadow-sm">
                                <div class="card-body row align-items-center">

                                    <div class="col-md-7">
                                        <h5 class="fw-bold text-dark mb-1">
                                            Khám với Bác Sĩ: <span
                                                class="text-primary-custom"><?php echo htmlspecialchars($app['doctor_name']); ?></span>
                                        </h5>
                                        <p class="text-muted small mb-1">
                                            Chuyên khoa: <?php echo htmlspecialchars($app['department_name']); ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="far fa-calendar-alt me-2 text-primary-custom"></i>
                                            Ngày: <span
                                                class="fw-bold"><?php echo date('d/m/Y', strtotime($app['appointment_date'])); ?></span>
                                        </p>
                                        <p class="mb-3">
                                            <i class="far fa-clock me-2 text-primary-custom"></i>
                                            Giờ: <span
                                                class="fw-bold"><?php echo date('H:i', strtotime($app['appointment_time'])); ?></span>
                                        </p>

                                        <p class="small fst-italic text-secondary mb-0">
                                            Lý do khám:
                                            <?php echo htmlspecialchars(substr($app['reason_for_visit'], 0, 100)) . '...'; ?>
                                        </p>
                                    </div>

                                    <div class="col-md-5 text-md-end mt-3 mt-md-0">
                                        <div class="mb-3">
                                            <span class="text-muted small d-block">Trạng Thái</span>
                                            <?php echo get_status_badge($app['status']); ?>
                                        </div>

                                        <?php if ($app['status'] === 'Pending'): ?>
                                            <form method="POST" action="lich-hen.php"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn hủy lịch hẹn này không?');">
                                                <input type="hidden" name="appointment_id"
                                                    value="<?php echo $app['appointment_id']; ?>">
                                                <button type="submit" name="cancel_appointment"
                                                    class="btn btn-sm btn-outline-danger btn-cancel fw-bold">
                                                    <i class="fas fa-times me-1"></i> Hủy Lịch Hẹn
                                                </button>
                                            </form>
                                        <?php elseif ($app['status'] === 'Scheduled'): ?>
                                            <button class="btn btn-sm btn-primary disabled">
                                                <i class="fas fa-check me-1"></i> Đã Xác Nhận
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> Bạn chưa có lịch hẹn nào được đặt.
                    <p class="mt-2 mb-0"><a href="tim-bac-si.php" class="alert-link fw-bold">Tìm Bác Sĩ</a> để bắt đầu đặt
                        lịch ngay!</p>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script>
        const currentUserId = "<?php echo $_SESSION['user_id'] ?? ''; ?>";

        if (currentUserId) {
            var pusher = new Pusher('18b40fb67053da5ad353', {
                cluster: 'ap1'
            });

            var channel = pusher.subscribe('phong-kham');

            channel.bind('cap-nhat-trang-thai', function (data) {

                if (data.patient_id == currentUserId) {

                    var audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                    audio.play().catch(e => console.log("Audio play blocked"));
                    let div = document.createElement('div');
                    div.style.cssText = "position:fixed; top:20px; right:20px; background:#198754; color:#fff; padding:15px; border-radius:8px; z-index:9999; box-shadow:0 4px 12px rgba(0,0,0,0.3); font-weight:500; animation: slideIn 0.5s;";
                    div.innerHTML = `<i class="fas fa-check-circle"></i> ${data.message}`;
                    document.body.appendChild(div);

                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            });
        }
    </script>
</body>

</html>