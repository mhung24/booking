<?php
require_once 'config/connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'Khách';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tim-bac-si.php");
    exit();
}

$doctor_id = (int) $_GET['id'];
$default_avatar = './img/no_avatar.png';

global $pdo;

function render_stars(float $rating): string
{
    $html = '';
    $full_stars = floor($rating);
    $has_half = ($rating - $full_stars) >= 0.5;
    $empty_stars = 5 - ceil($rating);

    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '<i class="fas fa-star text-warning"></i>';
    }
    if ($has_half) {
        $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '<i class="far fa-star text-warning"></i>';
    }
    return $html;
}

$sql_doctor = "
    SELECT 
        D.doctor_id, D.full_name, D.profile_picture, D.biography, D.experience_years, 
        D.education, D.working_location, T.department_name, T.department_id,
        COALESCE(AVG(R.rating_score), 0) AS average_rating,
        COUNT(R.rating_id) AS total_reviews
    FROM Doctors D
    JOIN Departments T ON D.department_id = T.department_id
    LEFT JOIN Ratings R ON D.doctor_id = R.doctor_id
    WHERE D.doctor_id = :id AND D.status = 'ACTIVE'
    GROUP BY D.doctor_id, D.full_name, D.profile_picture, D.biography, D.experience_years, D.education, D.working_location, T.department_name, T.department_id
";
$stmt_doctor = $pdo->prepare($sql_doctor);
$stmt_doctor->execute([':id' => $doctor_id]);
$doctor = $stmt_doctor->fetch();

if (!$doctor) {
    header("Location: tim-bac-si.php");
    exit();
}

$doctor['rating'] = number_format((float) $doctor['average_rating'], 1);
$doctor['image'] = !empty($doctor['profile_picture']) ? $doctor['profile_picture'] : $default_avatar;

$sql_reviews = "
    SELECT patient_name, rating_score, review_text, rating_date
    FROM Ratings
    WHERE doctor_id = :id
    ORDER BY rating_date DESC
";
$stmt_reviews = $pdo->prepare($sql_reviews);
$stmt_reviews->execute([':id' => $doctor_id]);
$reviews = $stmt_reviews->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    if (!$is_logged_in) {
        $error_review = "Vui lòng đăng nhập để gửi đánh giá.";
    } else {
        $rating_score = (int) $_POST['rating_score'];
        $review_text = trim($_POST['review_text']);
        $patient_name = $_SESSION['user_name'];

        if ($rating_score < 1 || $rating_score > 5) {
            $error_review = "Điểm đánh giá không hợp lệ.";
        } else {
            try {
                $sql_insert_review = "
                    INSERT INTO Ratings (doctor_id, patient_id, patient_name, rating_score, review_text)
                    VALUES (:doctor_id, :patient_id, :patient_name, :rating_score, :review_text)
                ";
                $stmt_insert_review = $pdo->prepare($sql_insert_review);
                $stmt_insert_review->execute([
                    ':doctor_id' => $doctor_id,
                    ':patient_id' => $user_id,
                    ':patient_name' => $patient_name,
                    ':rating_score' => $rating_score,
                    ':review_text' => $review_text
                ]);
                $success_review = "Cảm ơn bạn đã gửi đánh giá!";
                header("Location: chi-tiet-bac-si.php?id=" . $doctor_id);
                exit();
            } catch (PDOException $e) {
                $error_review = "Lỗi khi gửi đánh giá: " . $e->getMessage();
            }
        }
    }
}
?>