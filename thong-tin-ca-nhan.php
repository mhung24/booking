<?php
require_once 'config/connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// === KIỂM TRA ĐĂNG NHẬP ===
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode('thong-tin-ca-nhan.php'));
    exit();
}

$patient_id = $_SESSION['user_id'];
$page_title = "Quản Lý Hồ Sơ Cá Nhân";

global $pdo;

$error_message = '';
$success_message = '';

// === XỬ LÝ CẬP NHẬT THÔNG TIN ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {

    $email = trim($_POST['email']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);

    // Kiểm tra tính hợp lệ cơ bản
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        $error_message = "Địa chỉ email không hợp lệ.";
    } else {
        try {
            $sql_update = "
                UPDATE Patients 
                SET email = :email, 
                    date_of_birth = :dob, 
                    gender = :gender, 
                    address = :address
                WHERE patient_id = :patient_id
            ";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                ':email' => empty($email) ? NULL : $email,
                ':dob' => empty($date_of_birth) ? NULL : $date_of_birth,
                ':gender' => empty($gender) ? NULL : $gender,
                ':address' => $address,
                ':patient_id' => $patient_id
            ]);

            $success_message = "Thông tin hồ sơ đã được cập nhật thành công.";

        } catch (PDOException $e) {
            // Kiểm tra lỗi trùng email
            if ($e->errorInfo[1] == 1062) {
                $error_message = "Email này đã được sử dụng bởi tài khoản khác. Vui lòng chọn email khác.";
            } else {
                $error_message = "Lỗi hệ thống khi cập nhật: " . $e->getMessage();
            }
        }
    }
}

// === TRUY VẤN THÔNG TIN BỆNH NHÂN HIỆN TẠI ===
$sql_patient = "
    SELECT full_name, phone_number, email, date_of_birth, gender, address
    FROM Patients
    WHERE patient_id = :patient_id
";
$stmt_patient = $pdo->prepare($sql_patient);
$stmt_patient->execute([':patient_id' => $patient_id]);
$patient = $stmt_patient->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    // Nếu không tìm thấy thông tin bệnh nhân (lỗi nghiêm trọng)
    header("Location: logout.php");
    exit();
}

$current_dob = $patient['date_of_birth'];
$current_gender = $patient['gender'];
$current_email = htmlspecialchars($patient['email'] ?? '');
$current_address = htmlspecialchars($patient['address'] ?? '');
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        .info-group {
            border-left: 4px solid #007bff;
            padding-left: 15px;
            margin-bottom: 25px;
        }

        .form-title {
            color: #198754;
            font-weight: 700;
            border-bottom: 2px solid #198754;
            padding-bottom: 5px;
            margin-bottom: 25px;
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <div class="container py-5">
            <div class="profile-container">
                <h1 class="fw-bolder text-center mb-5 text-primary">
                    <i class="fas fa-cog me-2"></i> <?php echo $page_title; ?>
                </h1>

                <?php if ($success_message): ?>
                    <div class="alert alert-success text-center"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="row mb-5">
                    <div class="col-md-6 info-group">
                        <label class="form-label fw-bold">Họ và Tên:</label>
                        <p class="lead mb-0 text-dark"><?php echo htmlspecialchars($patient['full_name']); ?></p>
                    </div>
                    <div class="col-md-6 info-group">
                        <label class="form-label fw-bold">Số Điện Thoại:</label>
                        <p class="lead mb-0 text-dark"><?php echo htmlspecialchars($patient['phone_number']); ?></p>
                    </div>
                    <div class="col-12 mt-3">
                        <p class="small text-muted fst-italic">Họ tên và Số điện thoại là thông tin định danh, không thể
                            thay đổi tại đây.</p>
                    </div>
                </div>

                <form action="thong-tin-ca-nhan.php" method="POST">
                    <h4 class="form-title">Cập Nhật Thông Tin Sức Khỏe</h4>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="gender" class="form-label fw-bold"><i
                                    class="fas fa-venus-mars me-2 text-primary"></i> Giới Tính</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">-- Chọn Giới Tính --</option>
                                <option value="Male" <?php echo ($current_gender == 'Male') ? 'selected' : ''; ?>>Nam
                                </option>
                                <option value="Female" <?php echo ($current_gender == 'Female') ? 'selected' : ''; ?>>Nữ
                                </option>
                                <option value="Other" <?php echo ($current_gender == 'Other') ? 'selected' : ''; ?>>Khác
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label fw-bold"><i
                                    class="fas fa-birthday-cake me-2 text-primary"></i> Ngày Sinh</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                value="<?php echo htmlspecialchars($current_dob ?? ''); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-bold"><i
                                    class="fas fa-envelope me-2 text-primary"></i> Email (Đăng ký)</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo $current_email; ?>" placeholder="VD: user@example.com">
                        </div>

                        <div class="col-md-12">
                            <label for="address" class="form-label fw-bold"><i
                                    class="fas fa-map-marker-alt me-2 text-primary"></i> Địa Chỉ</label>
                            <input type="text" class="form-control" id="address" name="address"
                                value="<?php echo $current_address; ?>" placeholder="VD: Số nhà, Tên đường, Quận/Huyện">
                        </div>

                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" name="update_profile" class="btn btn-success btn-lg fw-bold">
                            <i class="fas fa-save me-2"></i> LƯU THAY ĐỔI
                        </button>
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <a href="lich-hen.php" class="btn btn-outline-secondary">
                        <i class="far fa-calendar-check me-2"></i> Quay lại Lịch Hẹn
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>