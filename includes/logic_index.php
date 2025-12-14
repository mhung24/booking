<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/connect.php';

$page_title = "Trang Chủ - Hệ Thống Đặt Lịch Khám Bệnh";
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

$target_page = "tim-bac-si.php";

if (!$is_logged_in) {
    $redirect_url = urlencode($target_page);
    $final_link = "login.php?redirect=" . $redirect_url;
} else {
    $final_link = $target_page;
}
?>