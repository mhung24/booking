<?php
// Mật khẩu đầu vào
$password_to_hash = 'admin';

// 1. MÃ HÓA (Đăng ký)
$hashed_password = password_hash($password_to_hash, PASSWORD_DEFAULT);

echo "<h2>Chuỗi băm cho 'admin':</h2>";
echo "<b>" . htmlspecialchars($hashed_password) . "</b><br><br>";

// 2. XÁC THỰC (Đăng nhập)
$password_nhap_khi_dn = 'admin';

if (password_verify($password_nhap_khi_dn, $hashed_password)) {
    echo "<p style='color: green;'>✅ Xác thực THÀNH CÔNG! (Mật khẩu đúng)</p>";
} else {
    echo "<p style='color: red;'>❌ Xác thực THẤT BẠI!</p>";
}

// Thử với mật khẩu sai
$password_sai = 'hung321';
if (password_verify($password_sai, $hashed_password)) {
    echo "<p>Mật khẩu sai vẫn thành công?</p>";
} else {
    echo "<p style='color: red;'>✅ Xác thực THẤT BẠI! (Mật khẩu sai)</p>";
}
?>