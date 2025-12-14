<?php
require_once 'config/connect.php';
require_once 'includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

$page_title = "Xem Toàn Bộ Đội Ngũ Bác Sĩ";
$default_avatar = './img/no_avatar.png';

$filter_department = $_GET['chuyen_khoa'] ?? '';
$filter_name = trim($_GET['ten'] ?? '');

global $pdo;

$limit = 12;
$page = max(1, (int) ($_GET['p'] ?? 1));
$offset = ($page - 1) * $limit;

$stmt_all_departments = $pdo->prepare("SELECT department_name FROM Departments ORDER BY department_name ASC");
$stmt_all_departments->execute();
$all_departments = $stmt_all_departments->fetchAll();

$where_clauses = ["D.status = 'ACTIVE'"];
$params = [];

if (!empty($filter_department)) {
    $where_clauses[] = "T.department_name = :dept_name";
    $params[':dept_name'] = $filter_department;
}
if (!empty($filter_name)) {
    $where_clauses[] = "D.full_name LIKE :full_name";
    $params[':full_name'] = '%' . $filter_name . '%';
}
$where_sql = implode(' AND ', $where_clauses);

$sql_count = "SELECT COUNT(D.doctor_id) FROM Doctors D JOIN Departments T ON D.department_id = T.department_id WHERE $where_sql";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_doctors = $stmt_count->fetchColumn();
$total_pages = ceil($total_doctors / $limit);

$sql_doctors = "
    SELECT D.doctor_id, D.full_name, D.profile_picture, D.biography, T.department_name,
           COALESCE(AVG(R.rating_score), 0) AS average_rating, COUNT(R.rating_id) AS total_reviews
    FROM Doctors D
    JOIN Departments T ON D.department_id = T.department_id
    LEFT JOIN Ratings R ON D.doctor_id = R.doctor_id
    WHERE $where_sql
    GROUP BY D.doctor_id
    ORDER BY D.full_name ASC
    LIMIT :limit OFFSET :offset
";

$stmt_doctors = $pdo->prepare($sql_doctors);
foreach ($params as $key => $value) {
    $stmt_doctors->bindValue($key, $value);
}
$stmt_doctors->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_doctors->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_doctors->execute();
$doctors_list = $stmt_doctors->fetchAll();

foreach ($doctors_list as &$doctor) {
    $doctor['rating'] = number_format((float) $doctor['average_rating'], 1);
    $doctor['image'] = (!empty($doctor['profile_picture']) && file_exists($doctor['profile_picture'])) ? $doctor['profile_picture'] : $default_avatar;
    $doctor['short_bio'] = substr($doctor['biography'] ?? 'Chưa có mô tả.', 0, 100) . '...';
}
unset($doctor);
?>