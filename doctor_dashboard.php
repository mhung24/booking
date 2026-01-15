<?php require_once 'includes/logic_doctor_dashboard.php'; ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Bác sĩ - MEDI-CARE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/doctor_dashboard.css" rel="stylesheet">
</head>

<body>
    <div class="sidebar">
        <div class="brand"><i class="fas fa-heartbeat text-danger"></i> MEDI-CARE</div>
        <nav class="flex-grow-1">
            <a href="doctor_dashboard.php" class="nav-item active"><i class="fas fa-stethoscope"></i> Phòng khám</a>
            <a href="#" class="nav-item" data-bs-toggle="modal" data-bs-target="#workingScheduleModal"><i
                    class="fas fa-calendar-alt"></i> Lịch trực</a>
            <a href="doctor_history.php" class="nav-item"><i class="fas fa-history"></i> Lịch sử ca khám</a>
        </nav>
        <div class="doctor-profile p-3 border-top">
            <div class="fw-bold small"><?= htmlspecialchars($doctor_name_display) ?></div>
            <a href="logout.php" class="text-danger small text-decoration-none"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>

    <div class="main-content">
        <div class="container-fluid p-0 py-4 px-3">
            <div class="row mb-4 animate-card">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-1">Xin chào, <?= htmlspecialchars($doctor_name_display) ?>! 👋</h3>
                        <p class="text-muted mb-0">Chúc bạn một ngày làm việc hiệu quả.</p>
                    </div>
                    <div class="bg-white p-3 rounded-4 shadow-sm border d-flex gap-3 align-items-center">
                        <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-circle">
                            <i class="fas fa-user-clock fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold"><?= count($waiting_patients) ?></h5>
                            <small class="text-muted fw-bold">ĐANG CHỜ</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <?php if ($examining_patient): ?>
                        <form method="POST" id="exam-form">
                            <input type="hidden" name="action" id="form_action" value="complete_exam">
                            <input type="hidden" name="appointment_id" value="<?= $examining_patient['appointment_id'] ?>">

                            <div class="modern-card mb-4 overflow-hidden border-0 shadow-sm">
                                <div class="patient-header bg-primary p-4 text-white d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="patient-avatar-large bg-white text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center"
                                            style="width:55px;height:55px;font-size:22px;">
                                            <?= substr($examining_patient['full_name'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 fw-bold"><?= htmlspecialchars($examining_patient['full_name']) ?></h5>
                                            <small class="opacity-75">ID: #<?= $examining_patient['patient_id'] ?> |
                                                <?= (!empty($examining_patient['date_of_birth'])) ? (date('Y') - date('Y', strtotime($examining_patient['date_of_birth']))) : '??' ?> tuổi</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-light fw-bold"
                                            onclick="openHistoryModal(<?= $examining_patient['patient_id'] ?>, '<?= htmlspecialchars($examining_patient['full_name']) ?>')">LỊCH SỬ</button>
                                        <button type="button" class="btn btn-sm btn-light fw-bold" data-bs-toggle="modal"
                                            data-bs-target="#transferModal"
                                            data-id="<?= $examining_patient['appointment_id'] ?>"
                                            data-name="<?= htmlspecialchars($examining_patient['full_name']) ?>">CHUYỂN BS</button>
                                    </div>
                                </div>
                                <div class="p-4 bg-white">
                                    <div class="row text-center">
                                        <div class="col-6 border-end"><small class="text-muted d-block">Dịch vụ chính</small><strong><?= $examining_patient['service_name'] ?></strong></div>
                                        <div class="col-6"><small class="text-muted d-block">Trạng thái</small><span
                                                class="badge <?= $examining_patient['status'] == 'WaitingResults' ? 'bg-info' : 'bg-success' ?>"><?= $examining_patient['status'] == 'WaitingResults' ? 'Chờ kết quả CLS' : 'Đang khám' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modern-card p-4 mb-4 border-0 shadow-sm bg-white">
                                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-microscope me-2"></i> CHỈ ĐỊNH CẬN LÂM SÀNG</h6>
                                <div class="input-group mb-3">
                                    <select id="service_select" class="form-select bg-light border-0">
                                        <option value="">-- Chọn dịch vụ CLS --</option>
                                        <?php foreach ($services_list as $sv): ?>
                                            <option value="<?= $sv['service_id'] ?>" data-name="<?= $sv['service_name'] ?>"
                                                data-price="<?= $sv['price'] ?>"><?= $sv['service_name'] ?> (<?= number_format($sv['price']) ?>đ)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-primary px-4 fw-bold" onclick="addServiceRow()">THÊM</button>
                                </div>
                                <div id="service-list-body" class="row g-2">
                                    <?php
                                    $stmt_old = $pdo->prepare("SELECT s.service_id, s.service_name FROM Service_Requests sr JOIN Services s ON sr.service_id = s.service_id WHERE sr.appointment_id = ? AND sr.status = 'Pending'");
                                    $stmt_old->execute([$examining_patient['appointment_id']]);
                                    foreach ($stmt_old->fetchAll() as $oc): ?>
                                        <div class="col-md-6">
                                            <div class="p-3 bg-light border rounded-3 d-flex justify-content-between align-items-center mb-2">
                                                <input type="hidden" name="service_ids[]" value="<?= $oc['service_id'] ?>">
                                                <div class="small fw-bold text-dark"><?= htmlspecialchars($oc['service_name']) ?> <span class="badge bg-info ms-1">Đã chọn</span></div>
                                                <button type="button" class="btn btn-sm text-danger" onclick="this.closest('.col-md-6').remove()">x</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="modern-card p-4 border-0 shadow-sm bg-white">
                                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-notes-medical me-2"></i> KẾT LUẬN & ĐƠN THUỐC</h6>
                                <textarea name="diagnosis" id="diagnosis_field" class="form-control mb-4 bg-light border-0"
                                    rows="3" placeholder="Nhập chẩn đoán..."><?= htmlspecialchars($examining_patient['diagnosis'] ?? '') ?></textarea>
                                
                                <div class="mt-3 mb-4">
                                    <label class="fw-bold text-primary small mb-1"><i class="fas fa-calendar-check me-1"></i> HẸN TÁI KHÁM</label>
                                    <input type="date" name="re_exam_date" class="form-control bg-light border-0" min="<?= date('Y-m-d') ?>" value="<?= $examining_patient['re_exam_date'] ?? '' ?>">
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-success small">ĐƠN THUỐC</span>
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold" onclick="addMedicineRow()">+ Thêm thuốc</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <tbody id="med-list-body"></tbody>
                                    </table>
                                </div>

                                <div class="mt-4 p-3 rounded-4 bg-light border-dashed">
                                    <h6 class="fw-bold mb-3 text-dark">TÓM TẮT CHI PHÍ (TẠM TÍNH)</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Phí khám cơ bản:</span>
                                        <span class="fw-bold">300,000đ</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Dịch vụ chính (<?= $examining_patient['service_name'] ?>):</span>
                                       <span class="fw-bold"><?= number_format($examining_patient['service_price'] ?? 0) ?>đ</span>
                                    </div>
                                    <div id="cls-cost-summary">
    <?php
    $sum_cls = 0;
    $stmt_sum = $pdo->prepare("SELECT s.service_name, s.price FROM Service_Requests sr JOIN Services s ON sr.service_id = s.service_id WHERE sr.appointment_id = ? AND sr.status = 'Pending'");
    $stmt_sum->execute([$examining_patient['appointment_id']]);
    $rows_cost = $stmt_sum->fetchAll();
    foreach ($rows_cost as $r_cost): 
        // Cộng dồn giá trị, nếu price null thì coi như là 0
        $sum_cls += ($r_cost['price'] ?? 0); 
    ?>
        <div class="d-flex justify-content-between mb-1 small text-info">
            <span>+ CLS: <?= htmlspecialchars($r_cost['service_name']) ?></span>
            <span><?= number_format($r_cost['price'] ?? 0) ?>đ</span>
        </div>
    <?php endforeach; ?>
</div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">TỔNG CỘNG:</h5>
                                        <h5 class="fw-bold text-danger mb-0"><?= number_format(300000 + $examining_patient['service_price'] + $sum_cls) ?>đ</h5>
                                    </div>
                                </div>

                                <div class="row g-3 mt-4">
                                    <div class="col-md-6"><button type="button" onclick="submitRequestServices()"
                                            class="btn btn-outline-primary w-100 py-3 fw-bold rounded-pill">YÊU CẦU CLS</button></div>
                                    <div class="col-md-6"><button type="button" onclick="submitCompleteExam()"
                                            class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow">HOÀN THÀNH & THANH TOÁN</button></div>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="modern-card p-5 text-center bg-white rounded-4 shadow-sm" style="min-height:500px;"><i
                                class="fas fa-user-md fa-4x text-light mb-3"></i>
                            <h5 class="text-muted">Phòng khám đang trống</h5>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="modern-card p-4 mb-4 border-0 shadow-sm bg-white">
                        <h6 class="fw-bold text-primary mb-3">DANH SÁCH ĐỢI (<?= count($waiting_patients) ?>)</h6>
                        <div style="max-height: 250px; overflow-y: auto;">
                            <?php foreach ($waiting_patients as $wp): ?>
                                <div class="p-3 mb-2 bg-light rounded-3 d-flex justify-content-between align-items-center border-start border-4 border-primary">
                                    <div class="small fw-bold"><?= htmlspecialchars($wp['full_name']) ?></div>
                                    <a href="doctor_dashboard.php?action=call&id=<?= $wp['appointment_id'] ?>"
                                        class="btn btn-sm btn-primary rounded-pill px-3">Gọi</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="modern-card p-4 border-0 shadow-sm bg-info bg-opacity-10 border-top border-info border-4">
                        <h6 class="fw-bold text-info mb-3">CHỜ KẾT QUẢ CLS (<?= count($waiting_results_list) ?>)</h6>
                        <div style="max-height: 350px; overflow-y: auto;">
                            <?php if (empty($waiting_results_list)) echo '<div class="text-center text-muted small">Trống</div>'; ?>
                            <?php foreach ($waiting_results_list as $rp): ?>
                                <div class="p-3 mb-2 bg-white rounded-3 border border-info shadow-sm d-flex justify-content-between align-items-center">
                                    <div class="small fw-bold text-info"><?= htmlspecialchars($rp['full_name']) ?></div>
                                    <a href="doctor_dashboard.php?id=<?= $rp['appointment_id'] ?>"
                                        class="btn btn-sm btn-info text-white rounded-pill px-3">Tiếp tục</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="workingScheduleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5><i class="fas fa-user-slash me-2"></i> Báo nghỉ & Đổi ca trực</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <form method="POST" class="mb-4 bg-white p-3 rounded shadow-sm border-start border-4 border-danger">
                        <input type="hidden" name="action" value="report_off">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="small fw-bold">Ngày muốn nghỉ:</label>
                                <input type="date" name="off_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-5">
                                <label class="small fw-bold">Ca trực:</label>
                                <select name="slot_name" class="form-select">
                                    <option value="Sáng">Sáng</option>
                                    <option value="Chiều">Chiều</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-danger w-100 fw-bold">BÁO NGHỈ</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive bg-white rounded shadow-sm">
                        <table class="table table-hover mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngày</th>
                                    <th>Ca trực</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($my_schedules)): ?>
                                    <tr><td colspan="3" class="text-center py-3 text-muted">Hệ thống chưa sắp xếp lịch trực cho bạn.</td></tr>
                                <?php else: foreach ($my_schedules as $s): ?>
                                    <tr class="<?= $s['status'] == 'Inactive' ? 'table-secondary opacity-50' : '' ?>">
                                        <td><?= date('d/m/Y', strtotime($s['work_date'])) ?></td>
                                        <td><?= $s['slot_name'] ?></td>
                                        <td>
                                            <?php if($s['status'] == 'Active'): ?>
                                                <span class="badge bg-success">Đang trực</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary text-decoration-line-through">Đã báo nghỉ</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="transferModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content border-0 shadow">
                <input type="hidden" name="action" value="transfer">
                <input type="hidden" name="appointment_id" id="transfer-app-id">
                <div class="modal-header bg-primary text-white"><h5>Chuyển BS tiếp nhận</h5></div>
                <div class="modal-body p-4">
                    <p>Bệnh nhân: <strong id="transfer-patient-name" class="text-primary"></strong></p>
                    <select name="target_doctor_id" class="form-select" required>
                        <option value="">-- Chọn BS --</option>
                        <?php foreach ($other_doctors as $doc): ?>
                            <option value="<?= $doc['doctor_id'] ?>">BS. <?= $doc['full_name'] ?> (<?= $doc['dept'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header"><h5>Lịch sử khám bệnh</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4" id="history-content"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        function addServiceRow() {
            const select = document.getElementById('service_select');
            if (!select.value) return;
            const serviceId = select.value;
            const opt = select.options[select.selectedIndex];
            const serviceName = opt.dataset.name;

            const existingInputs = document.querySelectorAll('input[name="service_ids[]"]');
            for (let input of existingInputs) {
                if (input.value === serviceId) {
                    alert("Dịch vụ '" + serviceName + "' đã có trong danh sách chỉ định!");
                    select.value = "";
                    return;
                }
            }

            const div = document.createElement('div');
            div.className = 'col-md-6';
            div.innerHTML = `
                <div class="p-3 bg-light border rounded-3 d-flex justify-content-between align-items-center mb-2">
                    <input type="hidden" name="service_ids[]" value="${serviceId}">
                    <div class="small fw-bold text-dark">${serviceName}</div>
                    <button type="button" class="btn btn-sm text-danger" onclick="this.closest('.col-md-6').remove()">x</button>
                </div>`;
            document.getElementById('service-list-body').appendChild(div);
            select.value = "";
        }

        function addMedicineRow() {
            const tbody = document.getElementById('med-list-body');
            const row = document.createElement('tr');
            let opts = '<option value="">-- Thuốc --</option>';
            <?php foreach ($medicines as $m): ?>
                opts += `<option value="<?= $m['medicine_id'] ?>"><?= htmlspecialchars($m['medicine_name']) ?></option>`;
            <?php endforeach; ?>
            row.innerHTML = `
                <td><select name="med_id[]" class="form-select form-select-sm border-0 bg-light">${opts}</select></td>
                <td><input type="number" name="quantity[]" class="form-control form-control-sm border-0 bg-light" value="1"></td>
                <td><input type="text" name="dosage[]" class="form-control form-control-sm border-0 bg-light" placeholder="Liều dùng"></td>
                <td><button type="button" class="btn btn-sm text-danger" onclick="this.closest('tr').remove()">x</button></td>`;
            tbody.appendChild(row);
        }

        function submitRequestServices() {
            if (document.getElementsByName('service_ids[]').length === 0) return alert("Chọn CLS!");
            document.getElementById('form_action').value = 'request_services';
            document.getElementById('exam-form').submit();
        }

        function submitCompleteExam() {
            if (!document.getElementById('diagnosis_field').value.trim()) return alert("Nhập chẩn đoán!");
            document.getElementById('form_action').value = 'complete_exam';
            document.getElementById('exam-form').submit();
        }

        function openHistoryModal(pid, name) {
            document.getElementById('history-content').innerHTML = 'Đang tải...';
            new bootstrap.Modal(document.getElementById('historyModal')).show();
            fetch(`doctor_dashboard.php?action=get_history&patient_id=${pid}`).then(r => r.text()).then(h => {
                document.getElementById('history-content').innerHTML = h;
            });
        }

        var pusher = new Pusher('18b40fb67053da5ad353', { cluster: 'ap1' });
        var channel = pusher.subscribe('phong-kham');
        channel.bind('bac-si-nhan-benh-nhan', function() { location.reload(); });

        document.getElementById('transferModal').addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            document.getElementById('transfer-app-id').value = button.getAttribute('data-id');
            document.getElementById('transfer-patient-name').textContent = button.getAttribute('data-name');
        });
    </script>
</body>

</html>