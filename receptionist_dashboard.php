<?php require_once 'includes/logic_receptionist_dashboard.php'; ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lễ Tân - Quản lý Phòng khám</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/receptionist_dashboard.css" rel="stylesheet">
    <style>
        .btn-disabled { pointer-events: none; opacity: 0.4; filter: grayscale(1); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand">
            <i class="fas fa-hospital-alt"></i> MEDI-CARE
        </div>
        
        <nav class="flex-grow-1">
            <a href="#" class="nav-item active">
                <i class="fas fa-calendar-check"></i> Lịch hẹn & Tiếp đón
            </a>
            <a href="receptionist_patients.php" class="nav-item">
    <i class="fas fa-users"></i> Hồ sơ Bệnh nhân
</a>
           
        </nav>

        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=Le+Tan&background=ef476f&color=fff" class="rounded-circle shadow-sm" width="40">
            <div style="line-height: 1.2;">
                <div class="fw-bold small"><?= htmlspecialchars($receptionist_name) ?></div>
                <small class="text-muted" style="font-size: 0.75rem;">Tiếp đón</small>
            </div>
            <a href="logout.php" class="ms-auto text-danger p-2" title="Đăng xuất"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>

    <div class="main-content">
        <div class="container-fluid p-0">
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h3 class="fw-bold mb-1">Xin chào, <?= htmlspecialchars($receptionist_name) ?>! 👋</h3>
                    <p class="text-muted">Quản lý tiếp đón và phân loại bệnh nhân.</p>
                </div>
                <div class="col-md-6 d-flex justify-content-end gap-3">
                    <div class="modern-card stat-card mb-0 py-2 px-3">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5"><?= count(array_filter($appointments, fn($a) => $a['status'] == 'Pending')) ?></div>
                            <small class="text-muted">Chờ xác nhận</small>
                        </div>
                    </div>
                    <div class="modern-card stat-card mb-0 py-2 px-3">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5"><?= count(array_filter($appointments, fn($a) => $a['status'] == 'Waiting')) ?></div>
                            <small class="text-muted">Chờ khám</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-card p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <form method="GET" action="receptionist_dashboard.php" class="d-flex gap-2 flex-grow-1" style="max-width: 600px;">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0" 
                               placeholder="Tìm tên hoặc SĐT..." value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                    <select name="status" class="form-select bg-light border-0" style="max-width: 180px;">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Chờ xác nhận</option>
                        <option value="Scheduled" <?= $status_filter === 'Scheduled' ? 'selected' : '' ?>>Đã xác nhận</option>
                        <option value="Waiting" <?= $status_filter === 'Waiting' ? 'selected' : '' ?>>Đang chờ khám</option>
                        <option value="Completed" <?= $status_filter === 'Completed' ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc</button>
                    <a href="receptionist_dashboard.php" class="btn btn-light" title="Làm mới"><i class="fas fa-sync-alt"></i></a>
                </form>

                <button class="btn btn-primary-gradient px-4 py-2 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#walkinCheckinModal">
                    <i class="fas fa-plus me-2"></i> Đăng ký Khám
                </button>
            </div>

            <div class="modern-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Bệnh nhân</th>
                                <th>Dịch vụ / Bác sĩ</th>
                                <th>Thời gian</th>
                                <th>Phân loại</th>
                                <th>Trạng thái</th>
                                <th class="text-end pe-4">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($appointments)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted opacity-50"><i class="far fa-folder-open fa-3x mb-3"></i><br>Không tìm thấy lịch hẹn nào.</div>
                                        </td>
                                    </tr>
                            <?php else: ?>
                                    <?php foreach ($appointments as $app): ?>
                                            <?php
                                            $is_locked = in_array($app['status'], ['Waiting', 'Examining', 'Completed', 'Cancelled']);
                                            ?>
                                            <tr class="<?= $app['is_emergency'] ? 'is-emergency' : '' ?>">
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-initial me-3">
                                                            <?= substr($app['patient_name'], 0, 1) ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark"><?= htmlspecialchars($app['patient_name']) ?></div>
                                                            <div class="small text-muted"><i class="fas fa-phone-alt me-1" style="font-size:0.7rem"></i><?= htmlspecialchars($app['phone_number']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($app['department_name']) ?></div>
                                                    <div class="small text-muted">BS. <?= htmlspecialchars($app['doctor_name']) ?></div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= date('H:i', strtotime($app['appointment_time'])) ?></div>
                                                    <div class="small text-muted"><?= date('d/m/Y', strtotime($app['appointment_date'])) ?></div>
                                                </td>
                                                <td>
                                                    <?php if ($app['is_emergency']): ?>
                                                            <span class="badge bg-danger rounded-pill"><i class="fas fa-ambulance"></i> Cấp cứu</span>
                                                    <?php elseif ($app['is_walkin']): ?>
                                                            <span class="badge bg-info text-dark bg-opacity-25 rounded-pill">Trực tiếp</span>
                                                    <?php else: ?>
                                                            <span class="badge bg-light text-dark border rounded-pill">Hẹn trước</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    switch ($app['status']) {
                                                        case 'Pending':
                                                            echo '<span class="badge badge-soft badge-soft-warning">Chờ xác nhận</span>';
                                                            break;
                                                        case 'Scheduled':
                                                            echo '<span class="badge badge-soft badge-soft-success">Đã xác nhận</span>';
                                                            break;
                                                        case 'Waiting':
                                                            echo '<span class="badge badge-soft badge-soft-info"><i class="fas fa-spinner fa-spin me-1"></i> Chờ khám</span>';
                                                            break;
                                                        case 'Examining':
                                                            echo '<span class="badge badge-soft badge-soft-primary">Đang khám</span>';
                                                            break;
                                                        case 'Completed':
                                                            echo '<span class="badge badge-soft badge-soft-dark">Đã xong</span>';
                                                            break;
                                                        case 'Cancelled':
                                                            echo '<span class="badge badge-soft badge-soft-danger">Đã hủy</span>';
                                                            break;
                                                        default:
                                                            echo '<span class="badge badge-soft badge-soft-dark">' . $app['status'] . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <?php if ($app['status'] === 'Pending'): ?>
                                                            <a href="confirm_payment.php?id=<?= $app['appointment_id'] ?>" 
                                                               class="btn btn-icon btn-success bg-opacity-10 text-success border-0 me-1" title="Xác nhận lịch">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                    <?php endif; ?>

                                                    <button class="btn btn-icon btn-primary bg-opacity-10 text-primary border-0 me-1 <?= $is_locked ? 'btn-disabled' : '' ?>" 
                                                            data-bs-toggle="modal" data-bs-target="#patientProfileModal" 
                                                            data-patient-id="<?= $app['patient_id'] ?>" title="Cập nhật & Gửi Bác sĩ">
                                                        <i class="fas fa-user-edit"></i>
                                                    </button>

                                                    <button class="btn btn-icon btn-danger bg-opacity-10 text-danger border-0 <?= $is_locked ? 'btn-disabled' : '' ?>" 
                                                            data-bs-toggle="modal" data-bs-target="#cancelModal"
                                                            data-appointment-id="<?= $app['appointment_id'] ?>"
                                                            data-patient-name="<?= htmlspecialchars($app['patient_name']) ?>" title="Hủy">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                    <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
        <?php if ($message): ?>
                <div class="alert alert-success shadow-lg border-0 d-flex align-items-center">
                    <i class="fas fa-check-circle me-2 fa-lg"></i><?= $message ?>
                    <button type="button" class="btn-close ms-3" data-bs-dismiss="alert"></button>
                </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
                <div class="alert alert-danger shadow-lg border-0 d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2 fa-lg"></i><?= $error_message ?>
                    <button type="button" class="btn-close ms-3" data-bs-dismiss="alert"></button>
                </div>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="receptionist_dashboard.php" class="modal-content border-0 shadow">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="appointment_id" id="modal-appointment-id">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">Xác nhận Hủy Lịch</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-times-circle text-danger fa-3x"></i>
                    </div>
                    <p class="text-center">Bạn có chắc muốn hủy lịch hẹn của bệnh nhân <strong id="modal-patient-name"></strong> không?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger px-4">Hủy ngay</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="patientProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i> Hồ sơ & Gửi Bác sĩ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light" id="patientProfileContent">
                    <div class="text-center py-5">
                        <i class="fas fa-circle-notch fa-spin fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/receptionist_dashboard.js"></script>
</body>
</html>