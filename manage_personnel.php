<?php
require_once 'includes/logic_manage_personnel.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhân sự</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/manage_personnel.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="dashboard-card">
        
        <div class="page-header">
            <div class="page-title">
                <h2>Danh Sách Nhân Sự</h2>
                <p>Quản lý Bác sĩ, Lễ tân và Quản trị viên hệ thống</p>
            </div>
            <div class="d-flex gap-2">
                <form method="GET" class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($search_keyword) ?>">
                </form>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle rounded-pill px-4" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-plus me-2"></i> Thêm Mới
                    </button>
                    <ul class="dropdown-menu shadow border-0">
                        <li><a class="dropdown-item py-2" href="create_doctor.php"><i class="fas fa-user-md me-2 text-primary"></i> Thêm Bác sĩ</a></li>
                        <li><a class="dropdown-item py-2" href="create_receptionist.php"><i class="fas fa-headset me-2 text-warning"></i> Thêm Lễ tân</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= $success_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Họ và Tên</th>
                        <th>Vai trò</th>
                        <th>Mã NV</th>
                        <th>Liên hệ</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($personnel_list)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Không tìm thấy dữ liệu.</td></tr>
                    <?php else: ?>
                        <?php foreach ($personnel_list as $p): ?>
                            <?php 
                                $badgeClass = match($p['role_type']) {
                                    'Doctor' => 'badge-doctor',
                                    'Receptionist' => 'badge-receptionist',
                                    default => 'badge-admin'
                                };
                            ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="avatar-circle"><?= mb_substr($p['full_name'], 0, 1) ?></div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($p['full_name']) ?></div>
                                            <div class="small text-muted">ID: #<?= $p['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="role-badge <?= $badgeClass ?>"><?= htmlspecialchars($p['role_display']) ?></span></td>
                                <td class="fw-bold text-secondary"><?= $p['code'] ?></td>
                                <td>
                                    <div class="d-flex flex-column small">
                                        <span><i class="fas fa-envelope me-2 text-muted"></i><?= htmlspecialchars($p['email']) ?></span>
                                        <span class="mt-1"><i class="fas fa-phone me-2 text-muted"></i><?= htmlspecialchars($p['phone_number']) ?></span>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-light text-primary me-1 btn-edit" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal"
                                            data-id="<?= $p['id'] ?>"
                                            data-role="<?= $p['role_type'] ?>"
                                            data-name="<?= htmlspecialchars($p['full_name']) ?>"
                                            data-email="<?= htmlspecialchars($p['email']) ?>"
                                            data-phone="<?= htmlspecialchars($p['phone_number']) ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button class="btn btn-sm btn-light text-danger btn-delete" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal"
                                            data-id="<?= $p['id'] ?>"
                                            data-role="<?= $p['role_type'] ?>"
                                            data-name="<?= htmlspecialchars($p['full_name']) ?>">
                                        <i class="fas fa-trash-alt"></i>
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

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Cập nhật thông tin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <input type="hidden" name="edit_role" id="edit_role">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Họ và Tên</label>
                        <input type="text" class="form-control" name="edit_name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Email</label>
                        <input type="email" class="form-control" name="edit_email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Số điện thoại</label>
                        <input type="text" class="form-control" name="edit_phone" id="edit_phone" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="update_personnel" class="btn btn-primary px-4">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Xác nhận xóa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-danger">
                        <i class="fas fa-trash-alt fa-3x"></i>
                    </div>
                    <h5 class="mb-3">Bạn có chắc chắn muốn xóa?</h5>
                    <p class="text-muted">Nhân sự: <strong id="delete_name_display" class="text-dark"></strong></p>
                    <p class="text-muted small">Hành động này không thể hoàn tác và dữ liệu sẽ mất vĩnh viễn.</p>
                    
                    <input type="hidden" name="id" id="delete_id">
                    <input type="hidden" name="role" id="delete_role">
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Không</button>
                    <button type="submit" name="delete_personnel" class="btn btn-danger px-4">Đồng ý Xóa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Xử lý Modal Sửa
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        // Nút đã bấm
        const button = event.relatedTarget;
        
        // Lấy dữ liệu từ data attributes
        const id = button.getAttribute('data-id');
        const role = button.getAttribute('data-role');
        const name = button.getAttribute('data-name');
        const email = button.getAttribute('data-email');
        const phone = button.getAttribute('data-phone');

        // Điền vào Form trong Modal
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_role').value = role;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_phone').value = phone;
    });

    // Xử lý Modal Xóa
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const role = button.getAttribute('data-role');
        const name = button.getAttribute('data-name');

        document.getElementById('delete_id').value = id;
        document.getElementById('delete_role').value = role;
        document.getElementById('delete_name_display').textContent = name;
    });
</script>

</body>
</html>