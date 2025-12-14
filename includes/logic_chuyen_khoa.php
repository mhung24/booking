<?php
require_once 'config/connect.php';

function render_stars(float $rating): string
{
    $html = '';
    $full_stars = floor($rating);
    $has_half = ($rating - $full_stars) >= 0.5;
    $empty_stars = 5 - ceil($rating);

    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    if ($has_half) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    return $html;
}

$selected_department = $_GET['dept'] ?? null;
$doctors_by_department = [];
$page_title = "Tất Cả Chuyên Khoa";
$default_avatar = './img/no_avatar.png';

global $pdo;

$sql_all_departments = "SELECT department_name, icon_class, description FROM Departments ORDER BY department_name ASC";
$stmt_all_departments = $pdo->prepare($sql_all_departments);
$stmt_all_departments->execute();
$all_departments = $stmt_all_departments->fetchAll();

if ($selected_department) {
    $page_title = "Bác Sĩ Chuyên Khoa " . $selected_department;
    $sql_doctors = "
        SELECT 
            D.doctor_id,
            D.full_name, 
            D.profile_picture, 
            D.biography,
            T.department_name
        FROM Doctors D
        JOIN Departments T ON D.department_id = T.department_id
        WHERE T.department_name = :dept_name AND D.status = 'ACTIVE'
        ORDER BY D.full_name ASC
    ";
    $stmt_doctors = $pdo->prepare($sql_doctors);
    $stmt_doctors->execute([':dept_name' => $selected_department]);
    $doctors_by_department = $stmt_doctors->fetchAll();

    foreach ($doctors_by_department as $key => $doctor) {
        $image_path = $doctor['profile_picture'];
        if (empty($image_path)) {
            $image_path = $default_avatar;
        }
        $doctors_by_department[$key]['image'] = $image_path;
        $doctors_by_department[$key]['rating'] = round(rand(40, 50) / 10, 1);
        // Lấy đoạn mô tả ngắn cho card
        $doctors_by_department[$key]['short_bio'] = substr($doctor['biography'], 0, 100) . '...';
    }
}
?>