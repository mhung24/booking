<?php
// FILE: includes/logic_receptionist_patients.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/connect.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

// Check quyền Lễ tân
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Receptionist') {
    header("Location: login.php");
    exit;
}
$receptionist_name = $_SESSION['receptionist_name'] ?? 'Lễ tân';

global $pdo;
$message = '';
$error_message = '';

// 1. XỬ LÝ: THÊM BỆNH NHÂN MỚI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_patient') {
    try {
        // Kiểm tra SĐT đã tồn tại chưa
        $stmt_check = $pdo->prepare("SELECT patient_id FROM Patients WHERE phone_number = :phone");
        $stmt_check->execute(['phone' => $_POST['phone_number']]);
        if ($stmt_check->rowCount() > 0) {
            $error_message = "Số điện thoại này đã được đăng ký cho bệnh nhân khác.";
        } else {
            // Mật khẩu mặc định là 123456 (hoặc SĐT)
            $default_pass = password_hash('123456', PASSWORD_DEFAULT);

            $sql = "INSERT INTO Patients (full_name, phone_number, gender, date_of_birth, bhyt_code, address, password) 
                    VALUES (:name, :phone, :gender, :dob, :bhyt, :addr, :pass)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'name' => $_POST['full_name'],
                'phone' => $_POST['phone_number'],
                'gender' => $_POST['gender'],
                'dob' => $_POST['date_of_birth'],
                'bhyt' => $_POST['bhyt_code'],
                'addr' => $_POST['address'],
                'pass' => $default_pass
            ]);
            $message = "Thêm hồ sơ bệnh nhân thành công!";
        }
    } catch (Exception $e) {
        $error_message = "Lỗi: " . $e->getMessage();
    }
}

// 2. XỬ LÝ: CẬP NHẬT THÔNG TIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_patient') {
    try {
        $sql = "UPDATE Patients SET full_name = :name, phone_number = :phone, gender = :gender, 
                date_of_birth = :dob, bhyt_code = :bhyt, address = :addr 
                WHERE patient_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $_POST['full_name'],
            'phone' => $_POST['phone_number'],
            'gender' => $_POST['gender'],
            'dob' => $_POST['date_of_birth'],
            'bhyt' => $_POST['bhyt_code'],
            'addr' => $_POST['address'],
            'id' => $_POST['patient_id']
        ]);
        $message = "Cập nhật hồ sơ thành công!";
    } catch (Exception $e) {
        $error_message = "Lỗi cập nhật: " . $e->getMessage();
    }
}

// 3. LẤY DANH SÁCH BỆNH NHÂN (TÌM KIẾM)
$keyword = $_GET['search'] ?? '';
$sql = "SELECT * FROM Patients WHERE 1=1";
$params = [];

if (!empty($keyword)) {
    $sql .= " AND (full_name LIKE :kw OR phone_number LIKE :kw OR bhyt_code LIKE :kw)";
    $params['kw'] = "%$keyword%";
}

$sql .= " ORDER BY patient_id DESC LIMIT 50"; // Lấy 50 người mới nhất
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>