<?php require_once 'includes/logic_doctor_dashboard.php'; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Bác sĩ - Hệ thống Khám bệnh</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <link href="css/doctor_dashboard.css" rel="stylesheet">
</head>

<body>

    <div class="sidebar">
        <div class="brand">
            <i class="fas fa-heartbeat text-danger"></i> MEDI-CARE
        </div>

        <nav class="flex-grow-1">
            <a href="doctor_dashboard.php" class="nav-item active">
                <i class="fas fa-stethoscope"></i> Phòng khám
            </a>
            <a href="#" class="nav-item" data-bs-toggle="modal" data-bs-target="#workingScheduleModal">
                <i class="fas fa-calendar-alt"></i> Lịch làm việc
            </a>
            <a href="doctor_history.php" class="nav-item">
                <i class="fas fa-history"></i> Lịch sử ca khám
            </a>
        </nav>

        <div class="doctor-profile">
            <img src="https://ui-avatars.com/api/?name=BS+DieuTri&background=0d6efd&color=fff"
                class="rounded-circle shadow-sm" width="40" height="40">
            <div style="line-height: 1.2;">
                <div class="fw-bold small">BS. Điều Trị</div>
                <small class="text-muted" style="font-size: 0.75rem;">Đa Khoa</small>
            </div>
            <a href="logout.php" class="ms-auto text-danger p-2" title="Đăng xuất"><i
                    class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>

    <div class="main-content">
        <div class="container-fluid p-0">

            <div class="row mb-4 animate-card">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-1">Xin chào, <?= htmlspecialchars($doctor_name_display ?? 'Bác sĩ') ?>! 👋
                        </h3>
                        <p class="text-muted mb-0">Chúc bạn một ngày làm việc hiệu quả.</p>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="bg-white p-3 rounded-4 shadow-sm d-flex align-items-center gap-3 border">
                            <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-circle">
                                <i class="fas fa-user-clock fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold"><?= count($waiting_patients) ?></h5>
                                <small class="text-muted fw-bold"
                                    style="font-size: 0.75rem; text-transform: uppercase;">Đang chờ</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">

                <div class="col-lg-8">
                    <?php if ($examining_patient): ?>
                        <form method="POST" action="doctor_dashboard.php" class="animate-card">
                            <input type="hidden" name="action" value="complete_exam">
                            <input type="hidden" name="appointment_id" value="<?= $examining_patient['appointment_id'] ?>">

                            <div class="modern-card mb-4 overflow-hidden">
                                <div class="patient-header d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="patient-avatar-large shadow">
                                            <?= substr($examining_patient['full_name'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold text-white">
                                                <?= htmlspecialchars($examining_patient['full_name']) ?></h4>
                                            <div class="opacity-75 small">
                                                <i class="fas fa-id-card me-1"></i> ID:
                                                #<?= $examining_patient['patient_id'] ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-white text-info btn-sm fw-bold shadow-sm"
                                            onclick="openHistoryModal(<?= $examining_patient['patient_id'] ?>, '<?= htmlspecialchars($examining_patient['full_name']) ?>')">
                                            <i class="fas fa-history me-1"></i> Lịch sử
                                        </button>

                                        <button type="button" class="btn btn-white text-primary btn-sm fw-bold shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#transferModal"
                                            data-id="<?= $examining_patient['appointment_id'] ?>"
                                            data-name="<?= htmlspecialchars($examining_patient['full_name']) ?>">
                                            <i class="fas fa-exchange-alt me-1"></i> Chuyển BS
                                        </button>
                                    </div>
                                </div>

                                <div class="patient-info-grid">
                                    <div class="info-item">
                                        <label>Giới tính / Tuổi</label>
                                        <div>
                                            <i class="fas fa-venus-mars text-muted me-1"></i>
                                            <?= $examining_patient['gender'] ?>
                                            <span class="mx-2">|</span>
                                            <?= date('Y') - date('Y', strtotime($examining_patient['date_of_birth'])) ?>
                                            tuổi
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <label>Số điện thoại</label>
                                        <div><i class="fas fa-phone-alt text-muted me-1"></i>
                                            <?= $examining_patient['phone_number'] ?></div>
                                    </div>
                                    <div class="info-item">
                                        <label>Dịch vụ đăng ký</label>
                                        <div class="text-primary fw-bold"><?= $examining_patient['service_name'] ?></div>
                                    </div>
                                    <div class="info-item">
                                        <label>Bảo hiểm y tế</label>
                                        <div>
                                            <?= $examining_patient['bhyt_code'] ? '<span class="badge bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i> Có BHYT</span>' : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Không có</span>' ?>
                                        </div>
                                    </div>
                                    <div class="info-item" style="grid-column: span 2;">
                                        <label>Ghi chú từ Lễ tân</label>
                                        <div class="bg-light p-2 rounded text-muted fst-italic border small">
                                            <?= !empty($examining_patient['reason_for_visit']) ? $examining_patient['reason_for_visit'] : 'Không có ghi chú đặc biệt.' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modern-card p-4">
                                <div class="mb-4">
                                    <h6 class="header-title text-primary"><i class="fas fa-file-medical-alt me-2"></i> KẾT
                                        LUẬN & CHẨN ĐOÁN</h6>
                                    <textarea name="diagnosis" class="form-control form-control-modern" rows="3"
                                        placeholder="Nhập chẩn đoán bệnh, triệu chứng lâm sàng và lời dặn dò..."
                                        required></textarea>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="header-title text-success mb-0"><i class="fas fa-pills me-2"></i> KÊ ĐƠN
                                            THUỐC</h6>
                                        <button type="button"
                                            class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold"
                                            onclick="addMedicineRow()">
                                            <i class="fas fa-plus me-1"></i> Thêm thuốc
                                        </button>
                                    </div>

                                    <div class="table-responsive rounded-3 border">
                                        <table class="table table-prescription mb-0">
                                            <thead>
                                                <tr>
                                                    <th width="40%">Tên thuốc</th>
                                                    <th width="15%">Số lượng</th>
                                                    <th width="40%">Cách dùng</th>
                                                    <th width="5%" class="text-center">Xóa</th>
                                                </tr>
                                            </thead>
                                            <tbody id="med-list-body"></tbody>
                                        </table>
                                        <div id="empty-med-msg" class="text-center p-4 text-muted small fst-italic">
                                            <i class="fas fa-box-open mb-2"></i><br>
                                            Chưa có thuốc nào trong đơn. Nhấn "Thêm thuốc" để bắt đầu.
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid pt-2">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm fw-bold"
                                        onclick="return confirm('Xác nhận hoàn thành ca khám?')">
                                        <i class="fas fa-check-circle me-2"></i> HOÀN THÀNH & IN ĐƠN
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="modern-card d-flex flex-column align-items-center justify-content-center text-center p-5"
                            style="min-height: 500px;">
                            <div class="bg-light rounded-circle p-4 mb-3 shadow-sm">
                                <i class="fas fa-user-md fa-4x text-secondary opacity-25"></i>
                            </div>
                            <h4 class="fw-bold text-dark">Phòng khám đang trống</h4>
                            <p class="text-muted">Vui lòng chọn bệnh nhân từ danh sách chờ bên phải để bắt đầu ca khám.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="modern-card p-4 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="header-title mb-0"><i class="fas fa-list-ul me-2"></i> DANH SÁCH CHỜ</h6>
                            <span
                                class="badge bg-warning text-dark rounded-pill px-3"><?= count($waiting_patients) ?></span>
                        </div>

                        <div class="flex-grow-1" style="overflow-y: auto; padding-right: 5px;">
                            <?php if (empty($waiting_patients)): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="far fa-smile fa-3x mb-3 opacity-50"></i><br>Không có bệnh nhân nào đang chờ.
                                </div>
                            <?php else: ?>
                                <?php foreach ($waiting_patients as $wp): ?>
                                    <div class="queue-item">
                                        <div class="d-flex align-items-center flex-grow-1"
                                            onclick="location.href='doctor_dashboard.php?action=call&id=<?= $wp['appointment_id'] ?>'">
                                            <div class="queue-avatar shadow-sm">
                                                <?= substr($wp['full_name'], 0, 1) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($wp['full_name']) ?></div>
                                                <div class="small text-muted text-truncate" style="max-width: 130px;">
                                                    <?= $wp['service_name'] ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="dropdown ms-2">
                                            <button class="btn btn-icon btn-sm text-muted" data-bs-toggle="dropdown"><i
                                                    class="fas fa-ellipsis-v"></i></button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                                <li><a class="dropdown-item fw-bold text-primary"
                                                        href="doctor_dashboard.php?action=call&id=<?= $wp['appointment_id'] ?>"><i
                                                            class="fas fa-bullhorn me-2"></i> Gọi khám</a></li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li><button class="dropdown-item text-info"
                                                        onclick="openHistoryModal(<?= $wp['patient_id'] ?>, '<?= htmlspecialchars($wp['full_name']) ?>')"><i
                                                            class="fas fa-history me-2"></i> Xem lịch sử</button></li>
                                                <li><button class="dropdown-item text-secondary"
                                                        onclick="openTransferModal(<?= $wp['appointment_id'] ?>, '<?= htmlspecialchars($wp['full_name']) ?>')"><i
                                                            class="fas fa-exchange-alt me-2"></i> Chuyển Bác sĩ</button></li>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="workingScheduleModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-calendar-check me-2"></i> Quản lý Lịch làm việc
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <form method="POST" action="doctor_dashboard.php" class="mb-4 p-3 bg-white rounded shadow-sm">
                        <input type="hidden" name="action" value="update_schedule">
                        <h6 class="fw-bold mb-3 text-primary">Đăng ký lịch mới</h6>
                        <div class="row align-items-end g-3">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold">Ngày làm việc:</label>
                                <input type="date" name="work_date" class="form-control" required
                                    min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold">Ca làm việc:</label>
                                <div class="d-flex gap-2">
                                    <div class="form-check border rounded p-2 flex-grow-1">
                                        <input class="form-check-input ms-0" type="checkbox" name="slots[]" value="Sáng"
                                            id="s1">
                                        <label class="form-check-label ms-1" for="s1">Sáng</label>
                                    </div>
                                    <div class="form-check border rounded p-2 flex-grow-1">
                                        <input class="form-check-input ms-0" type="checkbox" name="slots[]"
                                            value="Chiều" id="s2">
                                        <label class="form-check-label ms-1" for="s2">Chiều</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success w-100 fw-bold">Lưu</button>
                            </div>
                        </div>
                    </form>

                    <h6 class="fw-bold mb-3 text-dark">Lịch làm việc đã đăng ký (Gần đây)</h6>
                    <div class="table-responsive bg-white rounded shadow-sm" style="max-height: 300px;">
                        <table class="table table-hover mb-0 small">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Ngày</th>
                                    <th>Ca trực</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt_sch = $pdo->prepare("SELECT * FROM doctor_schedules WHERE doctor_id = :did AND work_date >= CURDATE() ORDER BY work_date ASC");
                                $stmt_sch->execute(['did' => $current_doctor_id]);
                                $my_schedules = $stmt_sch->fetchAll();

                                if (empty($my_schedules)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">Chưa có lịch làm việc.</td>
                                    </tr>
                                <?php else:
                                    foreach ($my_schedules as $s): ?>
                                        <tr>
                                            <td class="fw-bold"><?= date('d/m/Y', strtotime($s['work_date'])) ?></td>
                                            <td><span
                                                    class="badge bg-info bg-opacity-10 text-info px-3"><?= $s['slot_name'] ?></span>
                                            </td>
                                            <td><span class="text-success small fw-bold"><i
                                                        class="fas fa-check-circle me-1"></i>Hoạt động</span></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="scheduleSuccessModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center p-4 border-0 shadow">
                <div class="mb-3"><i class="fas fa-check-circle text-success fa-4x"></i></div>
                <h5 class="fw-bold">Thành công!</h5>
                <p class="text-muted small">Lịch làm việc đã được cập nhật.</p>
                <button type="button" class="btn btn-success w-100 fw-bold" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="transferModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="doctor_dashboard.php" class="modal-content border-0 shadow">
                <input type="hidden" name="action" value="transfer">
                <input type="hidden" name="appointment_id" id="transfer-app-id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-exchange-alt me-2"></i> Chuyển bệnh nhân</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <p class="mb-3">Bạn đang chuyển hồ sơ của: <strong id="transfer-patient-name"
                            class="text-primary"></strong></p>
                    <label class="form-label small fw-bold text-muted text-uppercase">Chọn Bác sĩ tiếp nhận:</label>
                    <select name="target_doctor_id" class="form-select form-select-lg mb-3 shadow-sm" required>
                        <option value="">-- Chọn Bác sĩ --</option>
                        <?php foreach ($other_doctors as $doc): ?>
                            <option value="<?= $doc['doctor_id'] ?>">BS. <?= $doc['full_name'] ?>
                                (<?= $doc['department_name'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer border-0 bg-white">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Xác nhận chuyển</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold text-primary"><i class="fas fa-history me-2"></i> Lịch sử khám bệnh
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    <h6 class="mb-4 pb-2 border-bottom">Bệnh nhân: <strong id="hist-patient-name"
                            class="text-dark"></strong></h6>
                    <div id="history-content">
                        <div class="text-center py-4"><i class="fas fa-spinner fa-spin text-primary"></i> Đang tải dữ
                            liệu...</div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'schedule_success'): ?>
                var successModal = new bootstrap.Modal(document.getElementById('scheduleSuccessModal'));
                successModal.show();
                document.getElementById('scheduleSuccessModal').addEventListener('hidden.bs.modal', function () {
                    var scheduleModal = new bootstrap.Modal(document.getElementById('workingScheduleModal'));
                    scheduleModal.show();
                });
            <?php endif; ?>
        });

        function addMedicineRow() {
            document.getElementById('empty-med-msg').style.display = 'none';
            const tbody = document.getElementById('med-list-body');
            const row = document.createElement('tr');

            let options = '<option value="">-- Chọn thuốc --</option>';
            <?php foreach ($medicines as $med): ?>
                options += `<option value="<?= $med['medicine_id'] ?>"><?= $med['medicine_name'] ?> (<?= $med['unit'] ?>)</option>`;
            <?php endforeach; ?>

            row.innerHTML = `
                <td><select name="med_id[]" class="form-select form-select-sm border-0 bg-light" required>${options}</select></td>
                <td><input type="number" name="quantity[]" class="form-control form-control-sm border-0 bg-light text-center" value="1" min="1" required></td>
                <td><input type="text" name="dosage[]" class="form-control form-control-sm border-0 bg-light" placeholder="Sáng 1, Tối 1..." required></td>
                <td class="text-center">
                    <button type="button" class="btn btn-link text-danger p-0 opacity-50 hover-opacity-100" onclick="removeRow(this)"><i class="fas fa-trash-alt"></i></button>
                </td>
            `;
            tbody.appendChild(row);
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            if (document.getElementById('med-list-body').children.length === 0) {
                document.getElementById('empty-med-msg').style.display = 'block';
            }
        }

        function openTransferModal(id, name) {
            document.getElementById('transfer-app-id').value = id;
            document.getElementById('transfer-patient-name').textContent = name;
            var myModal = new bootstrap.Modal(document.getElementById('transferModal'));
            myModal.show();
        }

        function openHistoryModal(patientId, patientName) {
            document.getElementById('hist-patient-name').textContent = patientName;
            document.getElementById('history-content').innerHTML = '<div class="text-center py-5"><i class="fas fa-circle-notch fa-spin fa-2x text-primary"></i><br>Đang tải dữ liệu...</div>';
            var myModal = new bootstrap.Modal(document.getElementById('historyModal'));
            myModal.show();

            fetch(`doctor_dashboard.php?action=get_history&patient_id=${patientId}`)
                .then(response => response.text())
                .then(html => { document.getElementById('history-content').innerHTML = html; })
                .catch(err => { document.getElementById('history-content').innerHTML = '<div class="text-center text-danger">Lỗi kết nối server!</div>'; });
        }

        var pusher = new Pusher('18b40fb67053da5ad353', { cluster: 'ap1' });
        var channel = pusher.subscribe('phong-kham');
        channel.bind('bac-si-nhan-benh-nhan', function (data) {
            let audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
            audio.play().catch(e => { });
            setTimeout(() => location.reload(), 1500);
        });
    </script>
</body>

</html>