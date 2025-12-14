<?php
// === CÀI ĐẶT DEBUGGING TỐI ĐA ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ===================================

require_once 'config/connect.php';

// Dữ liệu mẫu để chèn (Bạn phải đảm bảo các ID này TỒN TẠI trong bảng Patients và Doctors)
$test_patient_id = 1; // Thay bằng một ID bệnh nhân có thật
$test_doctor_id = 5;  // Thay bằng một ID bác sĩ có thật
$test_date = date('Y-m-d', strtotime('+3 days')); // Ngày hẹn sau 3 ngày
$test_time = '09:30:00';
$test_reason = 'Kiểm tra tổng quát do đau lưng kéo dài.';

global $pdo;

echo "<h1>Kiểm tra INSERT vào bảng Appointments</h1>";

// Lệnh SQL để kiểm tra
$sql_test = "
    INSERT INTO Appointments 
    (patient_id, doctor_id, appointment_date, appointment_time, reason_for_visit, status, created_at)
    VALUES (:patient_id, :doctor_id, :app_date, :app_time, :reason, 'Pending', NOW())
";

$test_params = [
    ':patient_id' => $test_patient_id,
    ':doctor_id' => $test_doctor_id,
    ':app_date' => $test_date,
    ':app_time' => $test_time,
    ':reason' => $test_reason
];

try {
    // === 1. THỰC THI LỆNH INSERT ===
    $stmt_test = $pdo->prepare($sql_test);
    $stmt_test->execute($test_params);

    // === 2. THÔNG BÁO THÀNH CÔNG ===
    echo "<div style='color: green; font-weight: bold;'>✅ THÀNH CÔNG! Dữ liệu đã được chèn vào bảng Appointments.</div>";
    echo "<p>Vui lòng kiểm tra lại bảng Appointments trong phpMyAdmin.</p>";

} catch (PDOException $e) {

    // === 3. BÁO LỖI NẾU THẤT BẠI ===
    echo "<div style='color: red; font-weight: bold;'>❌ THẤT BẠI KHI INSERT VÀO DATABASE!</div>";
    echo "<p>Lỗi PDO: " . htmlspecialchars($e->getMessage()) . " | SQLSTATE: " . htmlspecialchars($e->getCode()) . "</p>";

    // Tái tạo lại lệnh SQL để dễ dàng gỡ lỗi thủ công
    $debug_sql = $sql_test;
    foreach ($test_params as $key => $value) {
        $debug_sql = str_replace($key, "'" . addslashes($value) . "'", $debug_sql);
    }
    $debug_sql = str_replace("NOW()", "CURRENT_TIMESTAMP", $debug_sql);

    echo "<h3>Lệnh SQL đã chuẩn bị:</h3>";
    echo "<pre>" . htmlspecialchars($debug_sql) . "</pre>";
    echo "<p>Vui lòng thử chạy lệnh này thủ công trong phpMyAdmin.</p>";

}
?>