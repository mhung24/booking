<?php require_once 'includes/logic_receptionist_patients.php'; ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Hồ sơ Bệnh nhân</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/receptionist_dashboard.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="brand"><i class="fas fa-hospital-alt"></i> MEDI-CARE</div>
        <nav class="flex-grow-1">
            <a href="receptionist_dashboard.php" class="nav-item">
                <i class="fas fa-calendar-check"></i> Lịch hẹn & Tiếp đón
            </a>
            <a href="receptionist_patients.php" class="nav-item active"> <i class="fas fa-users"></i> Hồ sơ Bệnh nhân
            </a>
          
        </nav>
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=Le+Tan&background=ef476f&color=fff" class="rounded-circle" width="40">
            <div><div class="fw-bold small">Lễ Tân</div></div>
            <a href="logout.php" class="ms-auto text-danger p-2"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>

    <div class="main-content">
        <div class="container-fluid p-0">
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4"><i class="fas fa-check-circle me-2"></i><?= $message ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4"><i class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="row mb-4 align-items-end">
                <div class="col-md-6">
                    <h3 class="fw-bold mb-1">Hồ sơ Bệnh nhân</h3>
                    <p class="text-muted mb-0">Tra cứu và quản lý thông tin bệnh nhân.</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="modern-card d-inline-flex align-items-center px-4 py-2 mb-0">
                        <i class="fas fa-users fa-2x text-primary me-3"></i>
                        <div class="text-start">
                            <div class="h4 fw-bold mb-0"><?= count($patients) ?></div>
                            <small class="text-muted">Hồ sơ hiển thị</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-card p-3 d-flex justify-content-between gap-3">
                <form method="GET" class="d-flex flex-grow-1 gap-2" style="max-width: 500px;">
                    <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Tìm tên, SĐT, BHYT..." value="<?= htmlspecialchars($keyword) ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </form>
                <button class="btn btn-primary-gradient px-4 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                    <i class="fas fa-user-plus me-2"></i> Thêm Hồ sơ
                </button>
            </div>

            <div class="modern-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Họ và Tên</th>
                                <th>Thông tin liên hệ</th>
                                <th>Giới tính / Ngày sinh</th>
                                <th>BHYT</th>
                                <th class="text-end pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($patients)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">Không tìm thấy dữ liệu.</td></tr>
                            <?php else: ?>
                                <?php foreach ($patients as $p): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">#<?= $p['patient_id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-initial me-3 bg-light text-dark fw-bold" style="width:35px;height:35px;font-size:0.9rem">
                                                    <?= substr($p['full_name'], 0, 1) ?>
                                                </div>
                                                <div class="fw-bold"><?= htmlspecialchars($p['full_name']) ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div><i class="fas fa-phone-alt text-muted me-2" style="font-size:0.8rem"></i><?= $p['phone_number'] ?></div>
                                            <small class="text-muted d-block text-truncate" style="max-width: 200px;"><?= $p['address'] ?></small>
                                        </td>
                                        <td>
                                            <?= $p['gender'] ?> <span class="text-muted mx-1">|</span> <?= date('d/m/Y', strtotime($p['date_of_birth'])) ?>
                                        </td>
                                        <td>
                                            <?php if ($p['bhyt_code']): ?>
                                                <span class="badge badge-soft-success"><?= $p['bhyt_code'] ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-soft-dark text-muted">Không có</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-icon btn-light text-primary" 
                                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)">
                                                <i class="fas fa-pen"></i>
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

    <div class="modal fade" id="addPatientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" class="modal-content border-0 shadow">
                <input type="hidden" name="action" value="add_patient">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i> Tạo Hồ sơ Mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Giới tính</label>
                            <select name="gender" class="form-select">
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Ngày sinh</label>
                            <input type="date" name="date_of_birth" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Mã BHYT (Nếu có)</label>
                            <input type="text" name="bhyt_code" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Địa chỉ</label>
                            <input type="text" name="address" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-bold">Lưu Hồ sơ</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editPatientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" class="modal-content border-0 shadow">
                <input type="hidden" name="action" value="edit_patient">
                <input type="hidden" name="patient_id" id="edit_id">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i> Cập nhật Hồ sơ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Họ và Tên</label>
                            <input type="text" name="full_name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Số điện thoại</label>
                            <input type="text" name="phone_number" id="edit_phone" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Giới tính</label>
                            <select name="gender" id="edit_gender" class="form-select">
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Ngày sinh</label>
                            <input type="date" name="date_of_birth" id="edit_dob" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Mã BHYT</label>
                            <input type="text" name="bhyt_code" id="edit_bhyt" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Địa chỉ</label>
                            <input type="text" name="address" id="edit_addr" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-info text-white fw-bold">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEditModal(patient) {
            document.getElementById('edit_id').value = patient.patient_id;
            document.getElementById('edit_name').value = patient.full_name;
            document.getElementById('edit_phone').value = patient.phone_number;
            document.getElementById('edit_gender').value = patient.gender;
            document.getElementById('edit_dob').value = patient.date_of_birth;
            document.getElementById('edit_bhyt').value = patient.bhyt_code;
            document.getElementById('edit_addr').value = patient.address;
            
            new bootstrap.Modal(document.getElementById('editPatientModal')).show();
        }
    </script>
</body>
</html>