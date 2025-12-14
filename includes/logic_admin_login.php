<?php
$error_message = '';
$ADMIN_TABLE = 'Admins';
$PASSWORD_COLUMN = 'hashed_pass';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Vui lòng nhập đầy đủ Email và Mật khẩu.';
    } else {
        try {
            $sql_check = "SELECT admin_id, full_name, {$PASSWORD_COLUMN}, admin_role FROM {$ADMIN_TABLE} WHERE email = :email";
            $stmt = $pdo->prepare($sql_check);
            $stmt->execute(['email' => $email]);
            $admin_account = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin_account) {
                $hashed_password_from_db = $admin_account[$PASSWORD_COLUMN];

                if (password_verify($password, $hashed_password_from_db)) {
                    $role = $admin_account['admin_role'];

                    $_SESSION['admin_id'] = $admin_account['admin_id'];
                    $_SESSION['admin_name'] = $admin_account['full_name'];
                    $_SESSION['user_role'] = $role;

                    if ($role === 'Super') {
                        header("Location: admin_dashboard.php");
                    } elseif ($role === 'HR_Admin') {
                        header("Location: hr_personnel_management.php");
                    }
                    exit;
                } else {
                    $error_message = 'Email hoặc Mật khẩu không chính xác.';
                }
            } else {
                $error_message = 'Email hoặc Mật khẩu không chính xác.';
            }

        } catch (PDOException $e) {
            $error_message = 'Lỗi hệ thống database: ' . $e->getMessage();
        }
    }
}