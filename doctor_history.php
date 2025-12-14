<?php
// FILE: doctor_history.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/connect.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();
$current_doctor_id = $_SESSION['doctor_id'] ?? 1;

// 1. XỬ LÝ AJAX: XEM CHI TIẾT ĐƠN THUỐC (Khi bấm nút "Xem")
if (isset($_GET['action']) && $_GET['action'] === 'get_details' && isset($_GET['id'])) {
    $app_id = (int) $_GET['id'];

    // Lấy thông tin khám
    $stmt = $pdo->prepare("SELECT diagnosis FROM Appointments WHERE appointment_id = :id");
    $stmt->execute(['id' => $app_id]);
    $app = $stmt->fetch();

    // Lấy thuốc
    $stmt_med = $pdo->prepare("
        SELECT pd.*, m.medicine_name, m.unit 
        FROM Prescription_Details pd
        JOIN Medicines m ON pd.medicine_id = m.medicine_id
        WHERE pd.appointment_id = :aid
    ");
    $stmt_med->execute(['aid' => $app_id]);
    $meds = $stmt_med->fetchAll(PDO::FETCH_ASSOC);

    echo '<div class="p-2">';
    echo '<div class="mb-3"><strong class="text-primary">Kết luận/Chẩn đoán:</strong><div class="bg-light p-3 rounded mt-1 border">' . nl2br(htmlspecialchars($app['diagnosis'])) . '</div></div>';

    if (!empty($meds)) {
        echo '<strong class="text-success">Đơn thuốc đã kê:</strong>';
        echo '<table class="table table-sm table-bordered mt-2">';
        echo '<thead class="table-light"><tr><th>Thuốc</th><th>SL</th><th>Liều dùng</th></tr></thead><tbody>';
        foreach ($meds as $m) {
            echo "<tr>
                    <td>{$m['medicine_name']}</td>
                    <td class='text-center'>{$m['quantity']} {$m['unit']}</td>
                    <td>{$m['dosage']}</td>
                  </tr>";
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="text-muted fst-italic">Không kê đơn thuốc cho ca này.</div>';
    }
    echo '</div>';
    exit;
}

// 2. LẤY DANH SÁCH LỊCH SỬ (Tìm kiếm & Phân trang)
$keyword = $_GET['search'] ?? '';
$sql = "
    SELECT A.*, P.full_name, P.phone_number, P.bhyt_code, S.service_name
    FROM Appointments A
    JOIN Patients P ON A.patient_id = P.patient_id
    LEFT JOIN Services S ON A.service_id = S.service_id
    WHERE A.doctor_id = :did 
    AND A.status = 'Completed'
";

$params = ['did' => $current_doctor_id];

if ($keyword) {
    $sql .= " AND (P.full_name LIKE :kw OR P.phone_number LIKE :kw)";
    $params['kw'] = "%$keyword%";
}

$sql .= " ORDER BY A.appointment_date DESC, A.appointment_time DESC LIMIT 50"; // Lấy 50 ca gần nhất

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$history_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Lịch sử khám bệnh - Bác sĩ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="css/doctor_dashboard.css" rel="stylesheet">
</head>

<body>

    <div class="sidebar">
        <div class="brand"><i class="fas fa-heartbeat text-danger"></i> MEDI-CARE</div>
        <nav class="flex-grow-1">
            <a href="doctor_dashboard.php" class="nav-item">
                <i class="fas fa-stethoscope"></i> Phòng khám
            </a>
            <a href="doctor_history.php" class="nav-item active"> <i class="fas fa-history"></i> Lịch sử ca khám
            </a>
            <a href="#" class="nav-item"><i class="fas fa-pills"></i> Kho thuốc</a>
            <a href="#" class="nav-item"><i class="fas fa-chart-pie"></i> Báo cáo</a>
        </nav>
        <div class="doctor-profile">
            <img src="https://ui-avatars.com/api/?name=BS&background=0d6efd&color=fff" class="rounded-circle"
                width="40">
            <div>
                <div class="fw-bold small">BS. Điều Trị</div>
            </div>
            <a href="logout.php" class="ms-auto text-danger p-2"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>

    <div class="main-content">
        <div class="container-fluid p-0">

            <div class="d-flex justify-content-between align-items-end mb-4 animate-card">
                <div>
                    <h3 class="fw-bold mb-1">Lịch sử khám bệnh</h3>
                    <p class="text-muted mb-0">Danh sách các bệnh nhân bạn đã hoàn thành điều trị.</p>
                </div>

                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Tìm tên hoặc SĐT..."
                        value="<?= htmlspecialchars($keyword) ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="modern-card p-0 overflow-hidden animate-card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th>Thời gian</th>
                                <th>Bệnh nhân</th>
                                <th>Dịch vụ</th>
                                <th>Chẩn đoán (Tóm tắt)</th>
                                <th class="text-end pe-4">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($history_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Không tìm thấy dữ liệu.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($history_list as $h): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">#<?= $h['appointment_id'] ?></td>
                                        <td>
                                            <div class="fw-bold"><?= date('d/m/Y', strtotime($h['appointment_date'])) ?></div>
                                            <small
                                                class="text-muted"><?= date('H:i', strtotime($h['appointment_time'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($h['full_name']) ?></div>
                                            <small class="text-muted"><?= $h['phone_number'] ?></small>
                                        </td>
                                        <td><span
                                                class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25"><?= $h['service_name'] ?></span>
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                                <?= htmlspecialchars($h['diagnosis']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-secondary"
                                                onclick="viewDetails(<?= $h['appointment_id'] ?>, '<?= htmlspecialchars($h['full_name']) ?>')">
                                                <i class="fas fa-eye"></i> Xem
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

    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Chi tiết ca khám: <span id="modal-patient"
                            class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modal-body-content">
                    Đang tải...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewDetails(id, name) {
            document.getElementById('modal-patient').textContent = name;
            document.getElementById('modal-body-content').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-primary"></i></div>';

            var myModal = new bootstrap.Modal(document.getElementById('detailModal'));
            myModal.show();

            fetch(`doctor_history.php?action=get_details&id=${id}`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('modal-body-content').innerHTML = html;
                });
        }
    </script>
</body>

</html>