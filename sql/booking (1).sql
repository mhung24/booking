<?php
// ================= DEBUG =================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =========================================

require_once 'config/connect.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

global $pdo;

$patient_id = (int)($_GET['id'] ?? 0);
$update_message = '';
$update_error = '';
$patient = null;
$services = [];

if ($patient_id <= 0) {
    echo '<div class="alert alert-danger">Không tìm thấy mã Bệnh nhân hợp lệ.</div>';
    exit;
}

// --- HÀM GIẢ ĐỊNH TÍNH TỈ LỆ KHẤU TRỪ BHYT ---
function get_bhyt_coverage_rate($bhyt_code) {
    if (!empty($bhyt_code)) {
        return 0.80; 
    }
    return 0.00;
}
// ---------------------------------------------


// Tải danh sách Dịch vụ/Phí (Sử dụng cột 'price')
try {
    $stmt_services = $pdo->query("SELECT service_id, service_name, price FROM Services ORDER BY service_name");
    $services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $update_error = "Lỗi Database: Không thể tải danh sách Dịch vụ. Vui lòng kiểm tra bảng 'Services' và cột 'price'.";
}


// ===== XỬ LÝ CẬP NHẬT HỒ SƠ (POST REQUEST) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_patient'])) {
    
    $patient_name = trim($_POST['full_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $address = trim($_POST['address'] ?? '');
    $bhyt_code = trim($_POST['bhyt_code'] ?? '');
    
    $paid_amount = (float)($_POST['paid_amount'] ?? 0);
    $service_id = (int)($_POST['service_id'] ?? 0);
    $appointment_id = (int)($_POST['appointment_id'] ?? 0); 

    if (empty($patient_name) || empty($phone_number) || empty($date_of_birth) || empty($gender)) {
        $update_error = 'Vui lòng điền đầy đủ các thông tin bắt buộc.';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Cập nhật thông tin Bệnh nhân (chính)
            $sql_update_patient = "
                UPDATE Patients SET 
                    full_name = :name, 
                    phone_number = :phone, 
                    gender = :gender, 
                    date_of_birth = :dob, 
                    address = :address, 
                    bhyt_code = :bhyt
                WHERE patient_id = :id
            ";
            $stmt = $pdo->prepare($sql_update_patient);
            $stmt->execute([
                'name' => $patient_name,
                'phone' => $phone_number,
                'gender' => $gender,
                'dob' => $date_of_birth,
                'address' => $address,
                'bhyt' => $bhyt_code,
                'id' => $patient_id
            ]);
            
            // Cập nhật thông tin dịch vụ/phí cho LỊCH HẸN HIỆN TẠI 
            if ($appointment_id > 0) {
                 $sql_update_appointment = "
                    UPDATE Appointments SET
                        service_id = :sid,
                        paid_amount = :paid
                    WHERE appointment_id = :aid
                 ";
                 $stmt_app = $pdo->prepare($sql_update_appointment);
                 $stmt_app->execute([
                    'sid' => $service_id,
                    'paid' => $paid_amount,
                    'aid' => $appointment_id
                 ]);
            }

            $pdo->commit();
            $update_message = "Cập nhật hồ sơ thành công!";
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $update_error = 'Lỗi cập nhật database: ' . $e->getMessage();
        }
    }
}

// ===== TRUY VẤN LẠI DỮ LIỆU ĐỂ HIỂN THỊ =====
try {
    $stmt_patient = $pdo->prepare("SELECT * FROM Patients WHERE patient_id = :id");
    $stmt_patient->execute(['id' => $patient_id]);
    $patient = $stmt_patient->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo '<div class="alert alert-danger">Không tìm thấy bệnh nhân có ID này.</div>';
        exit;
    }
    
    $patient_dob = $patient['date_of_birth'] ? date('Y-m-d', strtotime($patient['date_of_birth'])) : '';

    // Lấy ID Lịch hẹn đang chờ xử lý gần nhất để cập nhật phí
    $stmt_app = $pdo->prepare("
        SELECT appointment_id, service_id, paid_amount 
        FROM Appointments 
        WHERE patient_id = :pid AND status IN ('Pending', 'Confirmed') 
        ORDER BY appointment_date DESC, appointment_time DESC LIMIT 1
    ");
    $stmt_app->execute(['pid' => $patient_id]);
    $current_appointment = $stmt_app->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Lỗi truy vấn: ' . $e->getMessage() . '</div>';
    exit;
}
?>

<div class="p-3">
    
    <?php if ($update_message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= $update_message ?></div>
        <script>
            setTimeout(function(){
                var modalElement = document.getElementById('patientProfileModal');
                if(modalElement) {
                    var modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                         modal.hide();
                    } else {
                        new bootstrap.Modal(modalElement).hide();
                    }
                }
                window.location.reload(); 
            }, 2000); 
        </script>
    <?php endif; ?>
    <?php if ($update_error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= $update_error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="update_patient" value="1">
        <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($current_appointment['appointment_id'] ?? 0) ?>">
        
        <div class="card mb-4 border-info">
            <div class="card-header bg-info text-white fw-bold"><i class="fas fa-user me-1"></i> Thông tin Cá nhân</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="full_name" class="form-label small fw-bold">Họ tên (*)</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                            value="<?= htmlspecialchars($patient['full_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone_number" class="form-label small fw-bold">Số điện thoại (*)</label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                            value="<?= htmlspecialchars($patient['phone_number']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="date_of_birth" class="form-label small fw-bold">Ngày sinh (*)</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                            value="<?= $patient_dob ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Giới tính (*)</label>
                        <div class="mt-1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="gender_male" value="Male" 
                                    <?= ($patient['gender'] === 'Male') ? 'checked' : '' ?> required>
                                <label class="form-check-label small" for="gender_male">Nam</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="gender_female" value="Female"
                                    <?= ($patient['gender'] === 'Female') ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="gender_female">Nữ</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="bhyt_code" class="form-label small fw-bold">Mã Thẻ BHYT</label>
                        <input type="text" class="form-control" id="bhyt_code" name="bhyt_code" 
                            value="<?= htmlspecialchars($patient['bhyt_code'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label small fw-bold">Địa chỉ (*)</label>
                        <textarea class="form-control" id="address" name="address" rows="1" required><?= htmlspecialchars($patient['address']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4 border-success">
            <div class="card-header bg-success text-white fw-bold"><i class="fas fa-money-check-alt me-1"></i> Dịch vụ & Thanh toán</div>
            <div class="card-body">
                
                <?php $bhyt_rate = get_bhyt_coverage_rate($patient['bhyt_code']); ?>
                <input type="hidden" id="bhyt_rate_edit" value="<?= $bhyt_rate ?>">

                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="service_id_edit" class="form-label small fw-bold">Loại Bệnh/Dịch vụ (*)</label>
                        <select class="form-select" id="service_id_edit" name="service_id" required>
                            <option value="" data-price="0">-- Chọn Loại dịch vụ --</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= htmlspecialchars($service['service_id']) ?>" 
                                        data-price="<?= htmlspecialchars($service['price']) ?>"
                                        <?= (isset($current_appointment['service_id']) && $current_appointment['service_id'] == $service['service_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($service['service_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Phí dịch vụ (*)</label>
                        <input type="text" class="form-control text-primary fw-bold" id="base_fee_display" readonly value=['price']>
                    </div>
                    
                    <input type="hidden" name="paid_amount" id="paid_amount_edit" value="<?= htmlspecialchars($current_appointment['paid_amount'] ?? 0) ?>">

                    <div class="col-12 mt-4">
                        <div class="alert alert-warning p-3 border-0 shadow-sm">
                            <div class="row">
                                <div class="col-8 small">Tỷ lệ BHYT chi trả (Dựa trên Mã BHYT):</div>
                                <div class="col-4 text-end fw-bold text-danger" id="bhyt_coverage_display"><?= ($bhyt_rate * 100) . '%' ?></div>
                            </div>
                            <div class="row mt-2 border-top pt-2">
                                <div class="col-8 fw-bold">TỔNG KHÁCH HÀNG CẦN THANH TOÁN:</div>
                                <div class="col-4 text-end fw-bold text-success fs-5" id="total_to_pay_display">0 VNĐ</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-info btn-lg">
                <i class="fas fa-save me-1"></i> Lưu Cập nhật Hồ sơ
            </button>
        </div>

    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectService = document.getElementById('service_id_edit');
        const paidAmountInput = document.getElementById('paid_amount_edit');
        
        const baseFeeDisplay = document.getElementById('base_fee_display');
        const totalToPayDisplay = document.getElementById('total_to_pay_display');
        const bhytRateInput = document.getElementById('bhyt_rate_edit');

        // Lấy tỷ lệ BHYT từ PHP
        const BHYT_RATE = parseFloat(bhytRateInput ? bhytRateInput.value : 0.00); 

        // Hàm định dạng tiền tệ
        const formatCurrency = (number) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(number);

        function calculateFee() {
            if (!selectService) return;

            const selectedOption = selectService.options[selectService.selectedIndex];
            const baseFee = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            
            let totalFee;
            
            if (BHYT_RATE > 0) {
                // Tính toán khấu trừ BHYT
                const patientShareRate = 1.00 - BHYT_RATE; 
                totalFee = baseFee * patientShareRate;
            } else {
                // Không BHYT, trả 100%
                totalFee = baseFee;
            }

            // [FIX]: Cập nhật hiển thị phí dịch vụ gốc
            baseFeeDisplay.value = formatCurrency(1000000);
            
            // Cập nhật hiển thị tổng khách hàng trả
            totalToPayDisplay.textContent = formatCurrency(totalFee);
            
            // Cập nhật giá trị gửi đi (Giá mà khách hàng phải trả)
            paidAmountInput.value = totalFee.toFixed(0); 
        }
        
        if (selectService) {
            // Đảm bảo calculateFee() được gọi ngay khi Modal tải
            calculateFee(); 
            selectService.addEventListener('change', calculateFee);
        }
    });
</script>