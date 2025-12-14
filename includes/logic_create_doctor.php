<?php
// FILE: includes/logic_create_doctor.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/connect.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

// 1. CHECK QUYỀN
$allowed_roles = ['Admin', 'HR_Admin', 'Super'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    die('<div class="alert alert-danger text-center m-5"><h1>⛔ TRUY CẬP BỊ TỪ CHỐI</h1><p>Bạn không có quyền thực hiện chức năng này.</p><a href="login.php">Đăng nhập lại</a></div>');
}

global $pdo;
$message = '';
$error_message = '';

// --- HÀM HỖ TRỢ: XÓA DẤU TIẾNG VIỆT ĐỂ TẠO EMAIL ---
function removeVietnameseAccents($str)
{
    $str = mb_strtolower($str, 'UTF-8');
    $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
    $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
    $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
    $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
    $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
    $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
    $str = preg_replace("/(đ)/", 'd', $str);
    $str = preg_replace("/[^a-z0-9\s]/", "", $str); // Bỏ ký tự đặc biệt
    $str = preg_replace('/\s+/', ' ', $str); // Xóa khoảng trắng thừa
    return trim($str);
}

// --- HÀM HỖ TRỢ: TẠO USERNAME TỪ TÊN (VD: thanh.nv) ---
function generateEmailPrefix($fullName)
{
    $cleanName = removeVietnameseAccents($fullName);
    $parts = explode(' ', $cleanName);
    $count = count($parts);

    if ($count == 0)
        return 'doctor';
    if ($count == 1)
        return $parts[0];

    // Lấy tên chính (phần cuối cùng)
    $firstName = $parts[$count - 1];

    // Lấy chữ cái đầu của Họ và Đệm
    $initials = '';
    for ($i = 0; $i < $count - 1; $i++) {
        $initials .= substr($parts[$i], 0, 1);
    }

    // Kết quả: ten.hodem (vd: thanh.nv)
    return $firstName . '.' . $initials;
}

// 2. HÀM TỰ ĐỘNG SINH MÃ BÁC SĨ (Cho cột license_number)
function generateLicenseNumber($pdo)
{
    do {
        // Tạo format L + 4 số + Chữ + Số (L0592X8)
        $part_num_4 = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $part_char = chr(rand(65, 90));
        $part_num_1 = rand(0, 9);
        $code = 'L' . $part_num_4 . $part_char . $part_num_1;

        // Check trùng trong DB (Check cột license_number)
        $stmt = $pdo->prepare("SELECT count(*) FROM Doctors WHERE license_number = ?");
        $stmt->execute([$code]);
        $exists = $stmt->fetchColumn();
    } while ($exists > 0);

    return $code;
}

// Tạo mã mẫu để hiển thị (nếu cần)
$auto_license_number = generateLicenseNumber($pdo);

// 3. LẤY DANH SÁCH KHOA
try {
    $stmt_dept = $pdo->query("SELECT department_id, department_name FROM Departments ORDER BY department_name");
    $departments = $stmt_dept->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Lỗi truy vấn khoa: ' . $e->getMessage();
    $departments = [];
}

// 4. XỬ LÝ SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_doctor'])) {

    $full_name = trim($_POST['full_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    // Cột license_code (Mã định danh hệ thống - BS...)
    // Lưu ý: Nếu cột này trong DB để UNIQUE thì không thể set cứng 'BS000000' cho tất cả mọi người được.
    // Tạm thời mình để ngẫu nhiên BS + số để tránh lỗi Duplicate Entry
    $license_code = 'BS' . rand(100000, 999999);

    // Cột license_number (Mã hành nghề - L...)
    // Nếu form không gửi hoặc readonly, tự sinh lại
    $license_number = !empty($_POST['license_code']) && strpos($_POST['license_code'], 'L') === 0
        ? trim($_POST['license_code'])
        : generateLicenseNumber($pdo);

    $department_id = (int) ($_POST['department_id'] ?? 0);
    $qualification = trim($_POST['qualification'] ?? '');

    // --- LOGIC TẠO EMAIL TỪ TÊN ---
    $emailPrefix = generateEmailPrefix($full_name);
    $email = $emailPrefix . '@benhvien.com';
    // ------------------------------

    if (empty($full_name) || empty($phone_number) || empty($qualification) || $department_id === 0) {
        $error_message = 'Vui lòng điền đầy đủ thông tin.';
    } else {
        try {
            // Check trùng SĐT
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Doctors WHERE phone_number = :phone");
            $stmt_check->execute(['phone' => $phone_number]);

            if ($stmt_check->fetchColumn() > 0) {
                $error_message = 'Số điện thoại này đã được sử dụng.';
            } else {
                $hashed_password = password_hash($phone_number, PASSWORD_DEFAULT);

                // Insert vào DB
                $sql_insert = "
                    INSERT INTO Doctors (full_name, license_code, department_id, license_number, phone_number, email, password_hash, education, created_at) 
                    VALUES (:full_name, :license_code, :department_id, :license_number, :phone_number, :email, :pass, :education, NOW())
                ";

                $stmt = $pdo->prepare($sql_insert);
                $stmt->execute([
                    'full_name' => $full_name,
                    'license_code' => $license_code,       // BSxxxxxx
                    'department_id' => $department_id,
                    'license_number' => $license_number,   // LxxxxXx
                    'phone_number' => $phone_number,
                    'email' => $email,                     // thanh.nv@benhvien.com
                    'pass' => $hashed_password,
                    'education' => $qualification
                ]);

                // Lưu thông báo và chuyển trang
                $_SESSION['success_message'] = "Đã thêm Bác sĩ: <strong>$full_name</strong><br>Mã hành nghề: $license_number<br>Email: $email";
                header("Location: hr_personnel_management.php");
                exit;
            }
        } catch (PDOException $e) {
            $error_message = 'Lỗi Database: ' . $e->getMessage();
        }
    }
}
?>