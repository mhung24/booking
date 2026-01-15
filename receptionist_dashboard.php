<?php require_once 'includes/logic_receptionist_dashboard.php'; ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lễ Tân - Quản lý Lịch hẹn</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/receptionist_dashboard.css" rel="stylesheet">
    <style>
        :root { --primary-gradient: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%); }
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fc; }
        .btn-disabled { pointer-events: none; opacity: 0.4; filter: grayscale(1); }
        .modern-card { border: none; border-radius: 16px; background: white; }
        .badge-soft { padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.75rem; }
        .badge-soft-warning { background: #fff8e1; color: #f59e0b; }
        .badge-soft-success { background: #ecfdf5; color: #10b981; }
        .badge-soft-info { background: #eff6ff; color: #3b82f6; }
        .badge-soft-danger { background: #fef2f2; color: #ef4444; }
        .table-custom th { text-transform: uppercase; font-size: 0.7rem; color: #64748b; border: none; }
    </style>
</head>
<body>

    <?php include 'includes/receptionist_sidebar.php'; ?>

    <div class="main-content p-4">
        <div class="container-fluid p-0">
            <div class="row mb-4 align-items-center">
                <div class="col-md-6">
                    <h3 class="fw-bold mb-1">Quản lý Tiếp đón 👋</h3>
                    <p class="text-muted mb-0">Hôm nay là <?= date('d/m/Y') ?></p>
                </div>
                <div class="col-md-6 d-flex justify-content-end gap-3">
                    <div class="modern-card shadow-sm py-2 px-4 d-flex align-items-center border-start border-4 border-warning">
                        <div class="me-3 bg-warning bg-opacity-10 p-2 rounded-3"><i class="fas fa-clock text-warning"></i></div>
                        <div>
                            <div class="fw-bold fs-5 mb-0"><?= count(array_filter($appointments, fn($a) => $a['status'] == 'Pending')) ?></div>
                            <small class="text-muted small fw-bold">CHỜ XÁC NHẬN</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-card p-3 d-flex flex-wrap align-items-center justify-content-between gap-3 shadow-sm mb-4">
                <form method="GET" class="d-flex gap-2 flex-grow-1" style="max-width: 800px;">
                    <div class="input-group flex-grow-1">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0" placeholder="Tìm tên hoặc SĐT..." value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                    <select name="status" class="form-select bg-light border-0" style="max-width: 200px;">
                        <option value="">-- Tất cả --</option>
                        <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Chờ xác nhận</option>
                        <option value="Waiting" <?= $status_filter === 'Waiting' ? 'selected' : '' ?>>Chờ khám</option>
                    </select>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">LỌC</button>
                </form>
            </div>

            <div class="modern-card shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small fw-bold">
                                <th class="ps-4 py-3">BỆNH NHÂN</th>
                                <th>CHUYÊN KHOA</th>
                                <th>THỜI GIAN</th>
                                <th>PHÂN LOẠI</th>
                                <th>TRẠNG THÁI</th>
                                <th class="text-end pe-4">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $app):
                                $is_locked = in_array($app['status'], ['Waiting', 'Examining', 'Completed', 'Cancelled']); ?>
                                    <tr class="<?= $app['is_emergency'] ? 'is-emergency' : '' ?>">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($app['patient_name']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($app['phone_number']) ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary small"><?= htmlspecialchars($app['department_name']) ?></div>
                                            <div class="small text-muted">BS. <?= htmlspecialchars($app['doctor_name']) ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= date('H:i', strtotime($app['appointment_time'])) ?></div>
                                            <div class="small text-muted"><?= date('d/m/Y', strtotime($app['appointment_date'])) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $app['is_emergency'] ? 'bg-danger' : ($app['is_walkin'] ? 'bg-info text-dark bg-opacity-25' : 'bg-light text-dark border') ?> rounded-pill px-3">
                                                <?= $app['is_emergency'] ? 'CẤP CỨU' : ($app['is_walkin'] ? 'TRỰC TIẾP' : 'HẸN TRƯỚC') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $s = $app['status'];
                                            $badge_class = match ($s) { 'Pending' => 'badge-soft-warning', 'Scheduled' => 'badge-soft-success', 'Waiting' => 'badge-soft-info', default => 'badge-soft-dark'};
                                            echo "<span class='badge-soft $badge_class'>$s</span>";
                                            ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <?php if ($app['status'] === 'Pending'): ?>
                                                        <a href="confirm_payment.php?id=<?= $app['appointment_id'] ?>" class="btn btn-sm btn-success rounded-circle"><i class="fas fa-check"></i></a>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-outline-primary rounded-circle <?= $is_locked ? 'btn-disabled' : '' ?>" data-bs-toggle="modal" data-bs-target="#patientProfileModal" data-patient-id="<?= $app['patient_id'] ?>"><i class="fas fa-user-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger rounded-circle <?= $is_locked ? 'btn-disabled' : '' ?>" data-bs-toggle="modal" data-bs-target="#cancelModal" data-appointment-id="<?= $app['appointment_id'] ?>" data-patient-name="<?= htmlspecialchars($app['patient_name']) ?>"><i class="fas fa-times"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content border-0 shadow">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="appointment_id" id="modal-appointment-id">
                <div class="modal-header bg-danger text-white border-0"><h5 class="modal-title fw-bold">Hủy Lịch Hẹn</h5></div>
                <div class="modal-body p-4 text-center">
                    <p>Xác nhận hủy lịch của bệnh nhân <strong id="modal-patient-name"></strong>?</p>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">XÁC NHẬN HỦY</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="patientProfileModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0"><h5 class="modal-title fw-bold">Tiếp nhận hồ sơ</h5></div>
                <div id="patientProfileContent" class="modal-body bg-light">
                    <div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/receptionist_dashboard.js"></script>
</body>
</html>