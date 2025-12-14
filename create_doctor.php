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

$message = '';
$error_message = '';

// Tên bảng và cột mật khẩu
$DOCTOR_TABLE = 'Doctors';
$PASSWORD_COLUMN = 'hashed_pass_1'; // Cột mật khẩu trong bảng Doctors

// --- ⚠️ BẢO VỆ TRANG CHỈ DÀNH CHO ADMIN VÀ HR_ADMIN ---
$allowed_roles = ['Super', 'HR_Admin'];

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    die('<div style="text-align:center; padding: 50px; color: red;"><h1>❌ TRUY CẬP BỊ TỪ CHỐI</h1><p>Bạn không có quyền quản lý tài khoản bác sĩ.</p></div>');
}
// --------------------------------------------------

// Tải danh sách Khoa (Departments)
try {
    $stmt_dept = $pdo->query("SELECT department_id, department_name FROM Departments ORDER BY department_name");
    $departments = $stmt_dept->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Lỗi truy vấn khoa: Vui lòng đảm bảo bảng Departments tồn tại. Chi tiết lỗi: ' . $e->getMessage();
    $departments = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_doctor'])) {

    // 1. Lấy dữ liệu
    $full_name = trim($_POST['full_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $license_code = trim($_POST['license_code'] ?? ''); // <--- TÊN ĐĂNG NHẬP MỚI
    $department_id = (int) ($_POST['department_id'] ?? 0);
    $qualification = trim($_POST['qualification'] ?? '');

    // Mật khẩu mặc định là SỐ ĐIỆN THOẠI
    $default_password = $phone_number;

    // 2. Kiểm tra dữ liệu
    if (empty($full_name) || empty($license_code) || empty($phone_number) || empty($qualification) || $department_id === 0) {
        $error_message = 'Vui lòng điền đầy đủ các trường thông tin Bác sĩ (bao gồm Mã hành nghề).';
    } else {
        try {

            // 3. KIỂM TRA TRÙNG MÃ HÀNH NGHỀ (Tên đăng nhập)
            // Giả sử cột tên đăng nhập là license_code
            $stmt_check_license = $pdo->prepare("SELECT COUNT(*) FROM {$DOCTOR_TABLE} WHERE license_code = :license_code");
            $stmt_check_license->execute(['license_code' => $license_code]);
            if ($stmt_check_license->fetchColumn() > 0) {
                $error_message = 'Mã hành nghề này đã được sử dụng cho tài khoản bác sĩ khác.';
            } else {

                // 4. MÃ HÓA MẬT KHẨU (SĐT)
                $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

                // 5. CHÈN VÀO BẢNG DOCTORS
                $sql_insert_d = "
                    INSERT INTO {$DOCTOR_TABLE} (full_name, email, phone_number, license_code, department_id, qualification, {$PASSWORD_COLUMN}) 
                    VALUES (:full_name, :email, :phone_number, :license_code, :department_id, :qualification, :hashed_pass)
                ";
                $stmt_insert_d = $pdo->prepare($sql_insert_d);
                $stmt_insert_d->execute([
                    'full_name' => $full_name,
                    'email' => $license_code . '@bv.com', // Dùng license_code để tạo email mặc định (nếu cần)
                    'phone_number' => $phone_number,
                    'license_code' => $license_code,
                    'department_id' => $department_id,
                    'qualification' => $qualification,
                    'hashed_pass' => $hashed_password
                ]);

                $message = "Đã tạo tài khoản Bác sĩ **{$full_name}** thành công! Tên đăng nhập là **{$license_code}**, Mật khẩu là **{$phone_number}**.";
            }

        } catch (PDOException $e) {
            $error_message = 'Lỗi database: Không thể tạo tài khoản. Vui lòng kiểm tra các cột trong bảng Doctors. Chi tiết lỗi: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Tạo Tài khoản Bác sĩ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .form-container {
            max-width: 650px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-create {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4"><i class="fas fa-user-md me-2"></i> Tạo Tài khoản Bác sĩ</h2>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?= $message ?>
                    <p class="mt-2 mb-0">⚠️ **Lưu ý:** Yêu cầu bác sĩ đổi mật khẩu ngay sau lần đăng nhập đầu tiên để bảo
                        mật.</p>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label">Họ tên Bác sĩ (*)</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="license_code" class="form-label">Mã hành nghề (Tên đăng nhập) (*)</label>
                        <input type="text" class="form-control" id="license_code" name="license_code" required>
                        <div class="form-text">Ví dụ: YK000123. Dùng để đăng nhập.</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone_number" class="form-label">Số điện thoại (*)</label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                        <div class="form-text">Dùng làm mật khẩu mặc định lần đầu.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_note" class="form-label">Mật khẩu</label>
                        <input type="text" class="form-control bg-light" id="password_note" value="Tự động đặt là SĐT"
                            disabled>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <label for="department_id" class="form-label">Chuyên khoa (*)</label>
                    <select class="form-select" id="department_id" name="department_id" required>
                        <option value="">-- Chọn Chuyên khoa --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                <?= htmlspecialchars($dept['department_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="qualification" class="form-label">Bằng cấp/Chuyên môn (*)</label>
                    <textarea class="form-control" id="qualification" name="qualification" rows="2"
                        placeholder="Ví dụ: Thạc sĩ Y khoa, Chứng chỉ Siêu âm..." required></textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="hr_personnel_management.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                    <button type="submit" name="create_doctor" class="btn btn-create btn-lg">
                        <i class="fas fa-user-plus me-1"></i> TẠO TÀI KHOẢN BÁC SĨ
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>