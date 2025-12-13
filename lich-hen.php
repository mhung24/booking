<?php
require_once 'config/connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode('lich-hen.php'));
    exit();
}

$patient_id = $_SESSION['user_id'];
$page_title = "Lịch Hẹn Của Tôi";

global $pdo;

$cancel_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_appointment'])) {
    $appointment_id_to_cancel = (int) $_POST['appointment_id'];

    try {

        $sql_cancel = "
            UPDATE Appointments 
            SET status = 'Cancelled'
            WHERE appointment_id = :app_id 
              AND patient_id = :patient_id 
              AND status = 'Pending'
        ";
        $stmt_cancel = $pdo->prepare($sql_cancel);
        $stmt_cancel->execute([
            ':app_id' => $appointment_id_to_cancel,
            ':patient_id' => $patient_id
        ]);

        if ($stmt_cancel->rowCount() > 0) {
            $cancel_message = ['type' => 'success', 'text' => 'Lịch hẹn đã được hủy thành công.'];
        } else {
            $cancel_message = ['type' => 'danger', 'text' => 'Không thể hủy lịch hẹn này (Lịch hẹn không tồn tại hoặc đã được xác nhận/hủy).'];
        }
    } catch (PDOException $e) {
        $cancel_message = ['type' => 'danger', 'text' => 'Lỗi hệ thống khi hủy lịch hẹn: ' . $e->getMessage()];
    }
}


$sql_appointments = "
    SELECT 
        A.appointment_id, A.appointment_date, A.appointment_time, A.reason_for_visit, A.status,
        D.full_name AS doctor_name, 
        T.department_name
    FROM Appointments A
    JOIN Doctors D ON A.doctor_id = D.doctor_id
    JOIN Departments T ON D.department_id = T.department_id
    WHERE A.patient_id = :patient_id
    ORDER BY A.appointment_date DESC, A.appointment_time DESC
";
$stmt_appointments = $pdo->prepare($sql_appointments);
$stmt_appointments->execute([':patient_id' => $patient_id]);
$appointments = $stmt_appointments->fetchAll();

function get_status_badge(string $status): string
{
    switch ($status) {
        case 'Pending':
            $class = 'bg-warning text-dark';
            $text = 'Chờ xác nhận';
            break;
        case 'Scheduled':
            $class = 'bg-primary';
            $text = 'Đã xác nhận';
            break;
        case 'Completed':
            $class = 'bg-success';
            $text = 'Đã hoàn thành';
            break;
        case 'Cancelled':
            $class = 'bg-danger';
            $text = 'Đã hủy';
            break;
        default:
            $class = 'bg-secondary';
            $text = 'Không rõ';
    }
    return "<span class='badge {$class}'>{$text}</span>";
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .appointment-card {
            border-left: 5px solid #007bff;
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .appointment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .status-header {
            background-color: #e9ecef;
            padding: 10px 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .text-primary-custom {
            color: #007bff !important;
        }

        .btn-cancel {
            transition: background-color 0.3s;
        }

        main {
            min-height: 516px;
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <div class="container py-5">
            <h1 class="fw-bolder text-center mb-5 text-primary-custom">
                <i class="far fa-calendar-check me-2"></i> <?php echo $page_title; ?>
            </h1>

            <?php if (!empty($cancel_message)): ?>
                <div class="alert alert-<?php echo $cancel_message['type']; ?> text-center mb-4">
                    <?php echo $cancel_message['text']; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($appointments)): ?>
                <div class="row">
                    <?php foreach ($appointments as $app): ?>
                        <div class="col-lg-12 mb-4">
                            <div class="card appointment-card shadow-sm">
                                <div class="card-body row align-items-center">

                                    <div class="col-md-7">
                                        <h5 class="fw-bold text-dark mb-1">
                                            Khám với Bác Sĩ: <span
                                                class="text-primary-custom"><?php echo htmlspecialchars($app['doctor_name']); ?></span>
                                        </h5>
                                        <p class="text-muted small mb-1">
                                            Chuyên khoa: <?php echo htmlspecialchars($app['department_name']); ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="far fa-calendar-alt me-2 text-primary-custom"></i>
                                            Ngày: <span
                                                class="fw-bold"><?php echo date('d/m/Y', strtotime($app['appointment_date'])); ?></span>
                                        </p>
                                        <p class="mb-3">
                                            <i class="far fa-clock me-2 text-primary-custom"></i>
                                            Giờ: <span
                                                class="fw-bold"><?php echo date('H:i', strtotime($app['appointment_time'])); ?></span>
                                        </p>

                                        <p class="small fst-italic text-secondary mb-0">
                                            Lý do khám:
                                            <?php echo htmlspecialchars(substr($app['reason_for_visit'], 0, 100)) . '...'; ?>
                                        </p>
                                    </div>

                                    <div class="col-md-5 text-md-end mt-3 mt-md-0">
                                        <div class="mb-3">
                                            <span class="text-muted small d-block">Trạng Thái</span>
                                            <?php echo get_status_badge($app['status']); ?>
                                        </div>

                                        <?php if ($app['status'] === 'Pending'): ?>
                                            <form method="POST" action="lich-hen.php"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn hủy lịch hẹn này không?');">
                                                <input type="hidden" name="appointment_id"
                                                    value="<?php echo $app['appointment_id']; ?>">
                                                <button type="submit" name="cancel_appointment"
                                                    class="btn btn-sm btn-outline-danger btn-cancel fw-bold">
                                                    <i class="fas fa-times me-1"></i> Hủy Lịch Hẹn
                                                </button>
                                            </form>
                                        <?php elseif ($app['status'] === 'Scheduled'): ?>
                                            <button class="btn btn-sm btn-primary disabled">
                                                <i class="fas fa-check me-1"></i> Đã Xác Nhận
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> Bạn chưa có lịch hẹn nào được đặt.
                    <p class="mt-2 mb-0"><a href="tim-bac-si.php" class="alert-link fw-bold">Tìm Bác Sĩ</a> để bắt đầu đặt
                        lịch ngay!</p>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>