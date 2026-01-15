<?php
// Tắt hiển thị lỗi trực tiếp ra màn hình để tránh làm hỏng chuỗi JSON
ini_set('display_errors', 0);
error_reporting(0);

require_once 'config/connect.php';

// Đảm bảo trình duyệt hiểu đây là dữ liệu JSON
header('Content-Type: application/json; charset=utf-8');

$doctor_id = isset($_GET['doctor_id']) ? (int) $_GET['doctor_id'] : 0;

if ($doctor_id > 0) {
    try {
        // Sử dụng $pdo từ file connect.php
        $stmt = $pdo->prepare("
            SELECT DISTINCT work_date 
            FROM doctor_schedules 
            WHERE doctor_id = :did AND work_date >= CURDATE()
            ORDER BY work_date ASC
        ");
        $stmt->execute(['did' => $doctor_id]);
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Trả về kết quả
        echo json_encode([
            'status' => 'success',
            'dates' => $dates
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Lỗi truy vấn cơ sở dữ liệu'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID bác sĩ không hợp lệ'
    ]);
}
exit;