<?php require_once 'includes/logic_receptionist_patients.php'; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Hồ sơ & Doanh thu - MEDI-CARE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/receptionist_dashboard.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fc;
        }

        .modern-card {
            border: none;
            border-radius: 16px;
            background: white;
            margin-bottom: 1.5rem;
        }

        .btn-primary-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
        }

        .table-custom th {
            text-transform: uppercase;
            font-size: 0.7rem;
            color: #64748b;
            background: #f8fafc;
            border: none;
            padding: 12px;
        }

        .avatar-initial {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .revenue-item {
            border-bottom: 1px dashed #e2e8f0;
            padding: 10px 0;
        }

        .revenue-item:last-child {
            border: none;
        }
    </style>
</head>

<body>

    <?php include 'includes/receptionist_sidebar.php'; ?>

    <div class="main-content p-4">
        <div class="container-fluid p-0">

            <?php if ($message || $error_message): ?>
                <div class="alert <?= $message ? 'alert-success' : 'alert-danger' ?> border-0 shadow-sm rounded-4">
                    <i
                        class="fas <?= $message ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i><?= $message ?: $error_message ?>
                </div>
            <?php endif; ?>

            <div class="row g-4 mb-4">
                <div class="col-lg-8" id="revenueSection">
                    <div class="modern-card shadow-sm p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie me-2 text-primary"></i>Chi
                                tiết doanh thu hôm nay</h5>
                            <span
                                class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold fs-6">
                                Tổng: <?= number_format($today_revenue ?? 0) ?>đ
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bệnh nhân</th>
                                        <th>Dịch vụ sử dụng</th>
                                        <th class="text-end">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($revenue_details)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted small italic">Chưa có giao
                                                dịch hoàn tất hôm nay.</td>
                                        </tr>
                                    <?php else:
                                        foreach ($revenue_details as $rev): ?>
                                            <tr>
                                                <td class="fw-bold small"><?= htmlspecialchars($rev['full_name']) ?></td>
                                                <td class="small text-muted">
                                                    <?= htmlspecialchars($rev['main_service']) ?>        <?= !empty($rev['extra_services']) ? ', ' . htmlspecialchars($rev['extra_services']) : '' ?>
                                                </td>
                                                <td class="text-end fw-bold text-primary small">
                                                    <?= number_format($rev['paid_amount']) ?>đ</td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="modern-card shadow-sm p-4 bg-primary-gradient text-white">
                        <div class="text-center py-3">
                            <div class="opacity-75 small fw-bold mb-2">DOANH THU THÁNG <?= date('m/Y') ?></div>
                            <h1 class="fw-800 mb-0"><?= number_format($month_revenue ?? 0) ?>đ</h1>
                            <hr class="my-4 opacity-25">
                            <div class="d-flex justify-content-around">
                                <div>
                                    <div class="small opacity-75">Hồ sơ mới</div>
                                    <div class="fw-bold fs-5"><?= count($patients) ?></div>
                                </div>
                                <div>
                                    <div class="small opacity-75">Lượt khám</div>
                                    <div class="fw-bold fs-5"><?= $total_completed_today ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-card shadow-sm p-4 border-start border-4 border-warning" id="reexamSection">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calendar-check me-2 text-warning"></i>Bệnh nhân chỉ định
                        tái khám hôm nay</h5>
                    <div class="small text-muted italic">Chỉ hiển thị bệnh nhân có lệnh tái khám từ Bác sĩ</div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr class="text-muted small fw-bold">
                                <th>BỆNH NHÂN</th>
                                <th>LIÊN HỆ</th>
                                <th>BÁC SĨ CHỈ ĐỊNH</th>
                                <th>CHẨN ĐOÁN</th>
                                <th class="text-end">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reexam_list)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">Không có lịch hẹn tái khám
                                        được chỉ định hôm nay.</td>
                                </tr>
                            <?php else:
                                foreach ($reexam_list as $re): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($re['full_name']) ?></div>
                                        </td>
                                        <td>
                                            <div class="badge bg-light text-dark fw-normal"><i
                                                    class="fas fa-phone me-2"></i><?= htmlspecialchars($re['phone_number']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small text-muted">BS. <?= htmlspecialchars($re['doctor_name']) ?></div>
                                        </td>
                                        <td class="small italic text-truncate" style="max-width: 200px;">
                                            <?= htmlspecialchars($re['diagnosis'] ?? 'Không có chẩn đoán') ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-warning btn-sm rounded-pill px-3 fw-bold shadow-sm"
                                                onclick="openReexamBooking(<?= htmlspecialchars(json_encode($re)) ?>)">
                                                HẸN LẠI LỊCH
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modern-card shadow-sm overflow-hidden mt-4">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
                    <h5 class="fw-bold mb-0">Quản lý Hồ sơ Bệnh nhân</h5>
                    <button class="btn btn-primary-gradient rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#addPatientModal">
                        <i class="fas fa-plus-circle me-2"></i>THÊM HỒ SƠ MỚI
                    </button>
                </div>
                <div class="p-3 bg-light border-bottom">
                    <form method="GET" class="row g-2">
                        <div class="col-md-10">
                            <input type="text" name="search" class="form-control border-0 shadow-sm"
                                placeholder="Tìm tên, SĐT hoặc mã BHYT..." value="<?= htmlspecialchars($keyword) ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark w-100 fw-bold">TÌM KIẾM</button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-custom">
                        <thead>
                            <tr>
                                <th class="ps-4">Bệnh nhân</th>
                                <th>Thông tin liên hệ</th>
                                <th>Bảo hiểm Y tế</th>
                                <th class="text-end pe-4">Tiếp đón</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $p): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initial me-3 bg-primary bg-opacity-10 text-primary">
                                                <?= mb_substr($p['full_name'], 0, 1) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($p['full_name']) ?>
                                                </div>
                                                <div class="text-muted small">ID: #<?= $p['patient_id'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold text-dark"><?= htmlspecialchars($p['phone_number']) ?>
                                        </div>
                                        <div class="text-muted small text-truncate" style="max-width: 200px;">
                                            <?= htmlspecialchars($p['address'] ?? 'N/A') ?></div>
                                    </td>
                                    <td><?= !empty($p['bhyt_code']) ? '<span class="badge bg-success bg-opacity-10 text-success fw-bold">' . $p['bhyt_code'] . '</span>' : '<span class="text-muted italic small">Trống</span>' ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button class="btn btn-outline-secondary btn-sm"
                                                onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)"><i
                                                    class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-primary btn-sm fw-bold px-3"
                                                onclick="openReceiveModal(<?= $p['patient_id'] ?>, '<?= htmlspecialchars($p['full_name']) ?>')">TIẾP
                                                NHẬN</button>
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

    <div class="modal fade" id="receivePatientModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered shadow-sm">
            <form method="POST" class="modal-content border-0">
                <input type="hidden" name="action" value="receive_patient">
                <input type="hidden" name="patient_id" id="receive_pid">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-bold">Tiếp nhận bệnh nhân</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p>Tiếp nhận bệnh nhân: <strong id="receive_pname" class="text-success"></strong></p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">CHỌN BÁC SĨ KHÁM</label>
                        <select name="doctor_id" class="form-select bg-light border-0" required>
                            <option value="">-- Chọn bác sĩ đang trực --</option>
                            <?php
                            $stmt_docs = $pdo->query("SELECT D.doctor_id, D.full_name, T.department_name FROM Doctors D JOIN Departments T ON D.department_id = T.department_id JOIN doctor_schedules DS ON D.doctor_id = DS.doctor_id WHERE DS.work_date = CURDATE() AND DS.status = 'Active'");
                            while ($d = $stmt_docs->fetch()): ?>
                                <option value="<?= $d['doctor_id'] ?>">BS. <?= $d['full_name'] ?>
                                    (<?= $d['department_name'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-success w-100 fw-bold rounded-pill">XÁC NHẬN VÀO HÀNG
                        ĐỢI</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="reexamBookingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content border-0 shadow-lg">
                <input type="hidden" name="action" value="rebook_reexam">
                <input type="hidden" name="patient_id" id="rebook_pid">
                <div class="modal-header bg-warning border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2"></i>Lên lịch tái khám</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">Hẹn tái khám cho: <strong id="rebook_pname" class="text-primary"></strong></p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">CHỌN BÁC SĨ KHÁM</label>
                        <select name="doctor_id" id="rebook_doctor_select" class="form-select bg-light border-0"
                            required></select>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">NGÀY KHÁM</label>
                            <input type="date" name="appointment_date" class="form-control border-0 bg-light"
                                value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">GIỜ KHÁM</label>
                            <input type="time" name="appointment_time" class="form-control border-0 bg-light"
                                value="08:00" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill">XÁC NHẬN ĐẶT LỊCH
                        HẸN</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="addPatientModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" class="modal-content border-0 shadow-lg">
                <input type="hidden" name="action" value="add_patient">
                <div class="modal-header bg-primary-gradient border-0 p-4">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-user-plus me-3"></i>Đăng ký Hồ sơ Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6"><label class="form-label small fw-bold text-muted uppercase">Họ và
                                Tên</label><input type="text" name="full_name"
                                class="form-control form-control-lg bg-light border-0" required></div>
                        <div class="col-md-6"><label class="form-label small fw-bold text-muted uppercase">Số điện
                                thoại</label><input type="text" name="phone_number"
                                class="form-control form-control-lg bg-light border-0" required></div>
                        <div class="col-md-6"><label class="form-label small fw-bold text-muted uppercase">Giới
                                tính</label><select name="gender" class="form-select form-select-lg bg-light border-0">
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                                <option value="Khác">Khác</option>
                            </select></div>
                        <div class="col-md-6"><label class="form-label small fw-bold text-muted uppercase">Ngày
                                sinh</label><input type="date" name="date_of_birth"
                                class="form-control form-control-lg bg-light border-0"></div>
                        <div class="col-md-6"><label class="form-label small fw-bold text-muted uppercase">Số thẻ
                                BHYT</label><input type="text" name="bhyt_code"
                                class="form-control form-control-lg bg-light border-0"></div>
                        <div class="col-md-6"><label class="form-label small fw-bold text-muted uppercase">Địa
                                chỉ</label><input type="text" name="address"
                                class="form-control form-control-lg bg-light border-0"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary-gradient px-5 rounded-pill fw-bold shadow">LƯU HỒ
                        SƠ</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editPatientModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" class="modal-content border-0 shadow-lg">
                <input type="hidden" name="action" value="edit_patient">
                <input type="hidden" name="patient_id" id="edit_id">
                <div class="modal-header bg-info text-white border-0 p-4">
                    <h5 class="modal-title fw-bold">Cập nhật Thông tin</h5><button type="button"
                        class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6"><label class="form-label small">HỌ VÀ TÊN</label><input type="text"
                                name="full_name" id="edit_name" class="form-control border-0 bg-light" required></div>
                        <div class="col-md-6"><label class="form-label small">SỐ ĐIỆN THOẠI</label><input type="text"
                                name="phone_number" id="edit_phone" class="form-control border-0 bg-light" required>
                        </div>
                        <div class="col-md-6"><label class="form-label small">GIỚI TÍNH</label><select name="gender"
                                id="edit_gender" class="form-select border-0 bg-light">
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                            </select></div>
                        <div class="col-md-6"><label class="form-label small">NGÀY SINH</label><input type="date"
                                name="date_of_birth" id="edit_dob" class="form-control border-0 bg-light"></div>
                        <div class="col-md-6"><label class="form-label small">MÃ BHYT</label><input type="text"
                                name="bhyt_code" id="edit_bhyt" class="form-control border-0 bg-light"></div>
                        <div class="col-md-6"><label class="form-label small">ĐỊA CHỈ</label><input type="text"
                                name="address" id="edit_addr" class="form-control border-0 bg-light"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0"><button type="submit"
                        class="btn btn-info text-white px-5 rounded-pill fw-bold">CẬP NHẬT</button></div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openReceiveModal(pid, pname) {
            document.getElementById('receive_pid').value = pid;
            document.getElementById('receive_pname').innerText = pname;
            new bootstrap.Modal(document.getElementById('receivePatientModal')).show();
        }

        function openReexamBooking(data) {
            document.getElementById('rebook_pid').value = data.patient_id;
            document.getElementById('rebook_pname').innerText = data.full_name;
            const select = document.getElementById('rebook_doctor_select');
            select.innerHTML = '';
            let optOld = document.createElement('option');
            optOld.value = data.doctor_id;
            optOld.text = `BS. ${data.doctor_name} (Bác sĩ chỉ định)`;
            optOld.selected = true;
            select.add(optOld);
            <?php
            $stmt_all_docs = $pdo->query("SELECT D.doctor_id, D.full_name, T.department_name FROM Doctors D JOIN Departments T ON D.department_id = T.department_id");
            $all_docs = $stmt_all_docs->fetchAll(PDO::FETCH_ASSOC);
            ?>
            const allDoctors = <?= json_encode($all_docs) ?>;
            allDoctors.forEach(doc => {
                if (doc.doctor_id != data.doctor_id) {
                    let opt = document.createElement('option');
                    opt.value = doc.doctor_id;
                    opt.text = `BS. ${doc.full_name} (${doc.department_name})`;
                    select.add(opt);
                }
            });
            new bootstrap.Modal(document.getElementById('reexamBookingModal')).show();
        }

        function openEditModal(p) {
            document.getElementById('edit_id').value = p.patient_id;
            document.getElementById('edit_name').value = p.full_name;
            document.getElementById('edit_phone').value = p.phone_number;
            document.getElementById('edit_gender').value = p.gender || 'Nam';
            document.getElementById('edit_dob').value = p.date_of_birth || '';
            document.getElementById('edit_bhyt').value = p.bhyt_code || '';
            document.getElementById('edit_addr').value = p.address || '';
            new bootstrap.Modal(document.getElementById('editPatientModal')).show();
        }
    </script>
</body>

</html>```